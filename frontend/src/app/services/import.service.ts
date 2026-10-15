import { HttpClient } from "@angular/common/http";
import { Injectable, inject } from "@angular/core";
import { Observable, tap } from "rxjs";
import { ImportQueuedResponse, ImportStatus, WorkerTaskList } from "../models/import";

export interface ImportResetResponse {
  status: "ok";
  message: string;
}

@Injectable({ providedIn: "root" })
export class ImportService {
  private readonly http = inject(HttpClient);

  create(file: File): Observable<ImportQueuedResponse> {
    const body = new FormData();
    body.append("file", file);

    console.info("[Import] Отправка файла на backend", {
      name: file.name,
      size: file.size,
      type: file.type,
    });

    return this.http.post<ImportQueuedResponse>("/api/imports", body).pipe(
      tap((response) => {
        console.info("[Import] Backend поставил файл в очередь", response);
      }),
    );
  }

  getStatus(importId: string): Observable<ImportStatus> {
    return this.http.get<ImportStatus>(`/api/imports/${importId}`).pipe(
      tap((status) => {
        const previousProcessed = this.processedByImport.get(importId) ?? 0;
        const processedDelta = status.processed - previousProcessed;
        if (processedDelta > 0) {
          console.info(
            `[Import ${importId}] Backend обработал строки ${previousProcessed + 1}-${status.processed} из ${status.total}`,
          );
        }
        this.processedByImport.set(importId, status.processed);

        console.debug(`[Import ${importId}] Статус backend`, {
          status: status.status,
          processed: status.processed,
          total: status.total,
          errors: this.parseErrors(status.errors).length,
        });

        if (status.status.startsWith("completed") || status.status === "failed") {
          console.info(`[Import ${importId}] Backend завершил импорт`, {
            status: status.status,
            processed: status.processed,
            total: status.total,
            errors: this.parseErrors(status.errors),
          });
          this.processedByImport.delete(importId);
        }
      }),
    );
  }

  listTasks(limit = 50): Observable<WorkerTaskList> {
    return this.http.get<WorkerTaskList>("/api/worker/tasks", {
      params: { limit },
    });
  }

  private readonly processedByImport = new Map<string, number>();

  private parseErrors(errors: ImportStatus["errors"]): ImportStatus["errors"] {
    return typeof errors === "string" ? JSON.parse(errors || "[]") : errors;
  }

  reset(): Observable<ImportResetResponse> {
    return this.http.post<ImportResetResponse>("/api/database/reset", {});
  }
}
