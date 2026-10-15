export interface ImportError {
  row: number;
  message: string;
}

export type ImportStatusName =
  | "queued"
  | "processing"
  | "completed"
  | "completed_with_errors"
  | "failed";

export interface ImportQueuedResponse {
  importId: string;
  status: "queued";
}

export interface ImportStatus {
  id: string;
  status: ImportStatusName;
  total: number;
  processed: number;
  errors: ImportError[] | string;
  created_at?: string;
  finished_at?: string;
}

export interface WorkerTaskList {
  items: ImportStatus[];
  total: number;
}
