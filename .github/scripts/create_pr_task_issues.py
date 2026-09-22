#!/usr/bin/env python3
"""Create idempotent GitHub issues from PR markers or manual workflow dispatch."""

from __future__ import annotations

import json
import os
import re
import sys
import urllib.error
import urllib.parse
import urllib.request
from pathlib import Path
from typing import Any


ROOT = Path(__file__).resolve().parents[2]
MANIFEST = ROOT / ".github/pr-issue-tasks.json"
MARKER_RE = re.compile(r"<!--\s*issue-tasks:\s*([a-z0-9][a-z0-9_-]*)\s*-->")
TASK_KEY_RE = re.compile(r"TASK-[0-9]+-[0-9]+-[0-9]{2}")
AD_HOC_KEY_RE = re.compile(r"PR-[A-Z0-9]+-[0-9]{3,}")


class AutomationError(Exception):
    pass


def api_request(method: str, path: str, payload: dict[str, Any] | None = None) -> Any:
    token = os.environ["GITHUB_TOKEN"]
    url = f"https://api.github.com/repos/{os.environ['GITHUB_REPOSITORY']}/{path}"
    data = json.dumps(payload).encode() if payload is not None else None
    request = urllib.request.Request(
        url,
        data=data,
        method=method,
        headers={
            "Accept": "application/vnd.github+json",
            "Authorization": f"Bearer {token}",
            "X-GitHub-Api-Version": "2022-11-28",
            "Content-Type": "application/json",
        },
    )
    try:
        with urllib.request.urlopen(request, timeout=20) as response:
            body = response.read()
            return json.loads(body) if body else None
    except urllib.error.HTTPError as error:
        detail = error.read().decode("utf-8", errors="replace")
        raise AutomationError(f"GitHub API {method} {path} failed ({error.code}): {detail}") from error


def load_manifest() -> dict[str, Any]:
    try:
        manifest = json.loads(MANIFEST.read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError) as error:
        raise AutomationError(f"Cannot read {MANIFEST.relative_to(ROOT)}: {error}") from error
    if not isinstance(manifest, dict) or manifest.get("version") != 1:
        raise AutomationError("Manifest must be an object with version: 1")
    if not isinstance(manifest.get("groups"), dict):
        raise AutomationError("Manifest groups must be an object")
    return manifest


def selected_group(event: dict[str, Any], groups: dict[str, Any]) -> tuple[str | None, list[dict[str, Any]]]:
    body = event.get("pull_request", {}).get("body") or ""
    markers = MARKER_RE.findall(body)
    if not markers:
        print("No issue-tasks marker in PR body; no issues created.")
        return None, []
    if len(markers) != 1:
        raise AutomationError("PR body must contain exactly one issue-tasks marker")
    group_key = markers[0]
    if group_key not in groups:
        raise AutomationError(f"Unknown issue task group: {group_key}")
    tasks = groups[group_key]
    if not isinstance(tasks, list):
        raise AutomationError(f"Group {group_key} must contain a task list")
    if not 1 <= len(tasks) <= 3:
        raise AutomationError(f"Group {group_key} must contain between 1 and 3 tasks")
    return group_key, tasks


def validate_tasks(tasks: list[dict[str, Any]]) -> None:
    seen: set[str] = set()
    for index, task in enumerate(tasks, start=1):
        if not isinstance(task, dict):
            raise AutomationError(f"Task {index} must be an object")
        key = task.get("key")
        source = task.get("source")
        if not isinstance(key, str) or key in seen:
            raise AutomationError(f"Task {index} must have a unique string key")
        seen.add(key)
        if source == "canonical":
            if not TASK_KEY_RE.fullmatch(key):
                raise AutomationError(f"Canonical task has invalid key: {key}")
            story = task.get("story")
            task_file = task.get("task_file")
            if not isinstance(story, str) or not story or not isinstance(task_file, str):
                raise AutomationError(f"Canonical task {key} needs story and task_file")
            relative = Path(task_file)
            if relative.is_absolute() or ".." in relative.parts or not task_file.startswith(
                "_bmad-output/planning-artifacts/epics/"
            ) or relative.name != "tasks.md":
                raise AutomationError(f"Canonical task {key} has an invalid task_file")
            source_path = ROOT / relative
            if relative.parent.name != story:
                raise AutomationError(f"Canonical task {key} story does not match its task_file")
            try:
                content = source_path.read_text(encoding="utf-8")
            except OSError as error:
                raise AutomationError(f"Cannot read task source {task_file}: {error}") from error
            if not re.search(rf"(?m)^\s*-\s+\[[ xX]\]\s+{re.escape(key)}:", content):
                raise AutomationError(f"{key} was not found as a task in {task_file}")
        elif source == "ad_hoc":
            if not AD_HOC_KEY_RE.fullmatch(key):
                raise AutomationError(f"Ad hoc task has invalid key: {key}")
            if not isinstance(task.get("reason"), str) or not task["reason"].strip():
                raise AutomationError(f"Ad hoc task {key} needs a reason")
        else:
            raise AutomationError(f"Task {key} source must be canonical or ad_hoc")
        if not isinstance(task.get("title"), str) or not task["title"].strip():
            raise AutomationError(f"Task {key} needs a non-empty title")
        if not isinstance(task.get("description"), str) or not task["description"].strip():
            raise AutomationError(f"Task {key} needs a non-empty description")
        if not isinstance(task.get("create", True), bool):
            raise AutomationError(f"Task {key} create must be boolean")
        labels = task.get("labels", [])
        if not isinstance(labels, list) or any(not isinstance(label, str) for label in labels):
            raise AutomationError(f"Task {key} labels must be a list of strings")


def all_issues() -> list[dict[str, Any]]:
    issues: list[dict[str, Any]] = []
    page = 1
    while True:
        query = urllib.parse.urlencode({"state": "all", "per_page": 100, "page": page})
        batch = api_request("GET", f"issues?{query}")
        if not batch:
            return issues
        issues.extend(item for item in batch if "pull_request" not in item)
        if len(batch) < 100:
            return issues
        page += 1


def validate_labels(tasks: list[dict[str, Any]]) -> None:
    requested = {
        label
        for task in tasks
        if task.get("create", True)
        for label in task.get("labels", [])
    }
    if not requested:
        return
    existing: set[str] = set()
    page = 1
    while True:
        query = urllib.parse.urlencode({"per_page": 100, "page": page})
        batch = api_request("GET", f"labels?{query}")
        existing.update(label["name"] for label in batch)
        if len(batch) < 100:
            break
        page += 1
    missing = sorted(requested - existing)
    if missing:
        raise AutomationError(f"Create these repository labels before using the group: {', '.join(missing)}")


def issue_marker(pr_number: int, key: str) -> str:
    return f"<!-- careerfitcv-pr-task: {pr_number}-{key} -->"


def manual_issue_marker(group_key: str, key: str) -> str:
    return f"<!-- careerfitcv-manual-task: {group_key}-{key} -->"


def make_issue_body(task: dict[str, Any], marker: str, pr: dict[str, Any] | None) -> str:
    lines = [
        f"Automation key: `{task['key']}`",
        f"Source: `{task['source']}`",
        "",
        task["description"].strip(),
    ]
    if task["source"] == "canonical":
        if pr is not None:
            canonical_url = f"{pr['base']['repo']['html_url']}/blob/{pr['base']['sha']}/{task['task_file']}"
        else:
            repository = os.environ["GITHUB_REPOSITORY"]
            revision = os.environ.get("GITHUB_SHA", "main")
            canonical_url = f"https://github.com/{repository}/blob/{revision}/{task['task_file']}"
        lines.extend(
            [
                f"Story: `{task['story']}`",
                f"Canonical task: [`{task['key']}`]({canonical_url})",
            ]
        )
    else:
        lines.append(f"Reason: {task['reason'].strip()}")
    if pr is not None:
        lines.append(f"Created for PR: [#{pr['number']}]({pr['html_url']})")
    else:
        lines.append("Created by manual workflow dispatch.")
    lines.extend(["", marker])
    return "\n".join(lines)


def upsert_pr_comment(pr_number: int, marker: str, message: str) -> None:
    page = 1
    comment_id = None
    while True:
        query = urllib.parse.urlencode({"per_page": 100, "page": page})
        batch = api_request("GET", f"issues/{pr_number}/comments?{query}")
        for comment in batch:
            if marker in (comment.get("body") or ""):
                comment_id = comment["id"]
                break
        if comment_id or len(batch) < 100:
            break
        page += 1
    payload = {"body": f"{message}\n\n{marker}"}
    if comment_id:
        api_request("PATCH", f"issues/comments/{comment_id}", payload)
    else:
        api_request("POST", f"issues/{pr_number}/comments", payload)


def run() -> None:
    event_path = Path(os.environ["GITHUB_EVENT_PATH"])
    event = json.loads(event_path.read_text(encoding="utf-8"))
    manifest = load_manifest()
    event_name = os.environ.get("GITHUB_EVENT_NAME", "pull_request_target")
    pr = event.get("pull_request")
    if event_name == "workflow_dispatch":
        requested_group = os.environ.get("ISSUE_TASK_GROUP", "")
        if not requested_group:
            raise AutomationError("Manual workflow dispatch requires ISSUE_TASK_GROUP")
        if requested_group == "epic-1":
            selected_groups = sorted(
                (key, tasks) for key, tasks in manifest["groups"].items() if key.startswith("story-1-")
            )
            if not selected_groups:
                raise AutomationError("Epic 1 has no story task groups")
        else:
            tasks = manifest["groups"].get(requested_group)
            if not isinstance(tasks, list):
                raise AutomationError(f"Unknown issue task group: {requested_group}")
            selected_groups = [(requested_group, tasks)]
    else:
        if not isinstance(pr, dict) or not isinstance(pr.get("number"), int):
            raise AutomationError("Expected a pull_request event payload")
        group_key, tasks = selected_group(event, manifest["groups"])
        if group_key is None:
            return
        selected_groups = [(group_key, tasks)]

    for group_key, tasks in selected_groups:
        if not 1 <= len(tasks) <= 3:
            raise AutomationError(f"Group {group_key} must contain between 1 and 3 tasks")
        validate_tasks(tasks)
        validate_labels(tasks)

    existing = all_issues()
    issue_urls: list[str] = []
    skipped_keys: list[str] = []
    for group_key, tasks in selected_groups:
        for task in tasks:
            if not task.get("create", True):
                print(f"Skipping {task['key']} because create is false")
                skipped_keys.append(task["key"])
                continue
            marker = (
                issue_marker(pr["number"], task["key"])
                if pr is not None
                else manual_issue_marker(group_key, task["key"])
            )
            match = next((item for item in existing if marker in (item.get("body") or "")), None)
            if match is None:
                payload = {
                    "title": task["title"].strip(),
                    "body": make_issue_body(task, marker, pr),
                    "labels": task.get("labels", []),
                }
                match = api_request("POST", "issues", payload)
                existing.append(match)
                print(f"Created {task['key']}: {match['html_url']}")
            else:
                print(f"Found existing {task['key']}: {match['html_url']}")
            issue_urls.append(f"- `{task['key']}`: {match['html_url']}")
    if pr is None:
        print("Task issues provisioned by manual workflow dispatch:")
        for issue_url in issue_urls:
            print(issue_url)
        return

    map_marker = f"<!-- careerfitcv-task-issue-map: {pr['number']} -->"
    summary = "\n".join(
        [
            "Task issues for this PR:",
            *(issue_urls or ["- No issues were created."]),
            *[f"- Skipped by configuration: `{key}`" for key in skipped_keys],
            "",
            "For canonical tasks, record the issue key and URL in the task's `tasks.md` External issue block.",
        ]
    )
    upsert_pr_comment(pr["number"], map_marker, summary)


if __name__ == "__main__":
    try:
        run()
    except (AutomationError, KeyError, json.JSONDecodeError) as error:
        print(f"::error::{error}", file=sys.stderr)
        sys.exit(1)
