import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { analyzeJobDescription, createJobDescription, deleteJobDescription, updateJobDescription } from '../api/jd.api'
import { createMatchReport } from '@/features/match/api/match.api'
import type { JobDescription, JdAnalysis } from '../types/jd.types'
import type { MatchReport } from '@/features/match/types/match.types'

export interface JdSaveRequest {
  generation: number
  jobDescriptionId: string
  revisionId: string
  data: { raw_text: string; company: string | null; role: string | null }
  submitted: { rawText: string; company: string; role: string }
  idempotencyKey: string
}

export interface JdAnalysisRequest {
  generation: number
  jobDescriptionId: string
  revisionId: string
  idempotencyKey: string
}

export interface JdMatchRequest {
  generation: number
  cvVersionId: string
  jobDescriptionId: string
  idempotencyKey: string
}

export interface JdDeleteRequest {
  generation: number
  jobDescriptionId: string
  revisionId: string
  idempotencyKey: string
}

interface WorkflowOptions {
  onAnalysisSuccess: (analysis: JdAnalysis, request: JdAnalysisRequest) => void
  onSaveSuccess: (jobDescription: JobDescription, request: JdSaveRequest) => void | Promise<void>
  onMatchSuccess: (report: MatchReport, request: JdMatchRequest) => void | Promise<void>
  onDeleteSuccess: (result: void, request: JdDeleteRequest) => void | Promise<void>
}

export function useJdWorkflowMutations(options: WorkflowOptions) {
  const queryClient = useQueryClient()
  const analysisMutation = useMutation({
    mutationFn: (request: JdAnalysisRequest) =>
      analyzeJobDescription(request.jobDescriptionId, request.idempotencyKey),
    onSuccess: options.onAnalysisSuccess,
  })
  const saveMutation = useMutation({
    mutationFn: (request: JdSaveRequest) => {
      return request.jobDescriptionId
        ? updateJobDescription(
            request.jobDescriptionId,
            request.revisionId,
            request.data,
            request.idempotencyKey,
          )
        : createJobDescription(request.data, request.idempotencyKey)
    },
    onSuccess: options.onSaveSuccess,
  })
  const matchMutation = useMutation({
    mutationFn: (request: JdMatchRequest) => {
      return createMatchReport(
        request.cvVersionId,
        request.jobDescriptionId,
        request.idempotencyKey,
      )
    },
    onSuccess: options.onMatchSuccess,
  })
  const deleteMutation = useMutation({
    mutationFn: (request: JdDeleteRequest) =>
      deleteJobDescription(
        request.jobDescriptionId,
        request.revisionId,
        request.idempotencyKey,
      ),
    onSuccess: options.onDeleteSuccess,
  })

  return { queryClient, analysisMutation, saveMutation, matchMutation, deleteMutation }
}
