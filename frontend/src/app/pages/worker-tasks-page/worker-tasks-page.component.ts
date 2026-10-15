import { CommonModule } from "@angular/common";
import { Component, inject } from "@angular/core";
import { FormsModule } from "@angular/forms";
import { catchError, map, of, startWith, switchMap, timer } from "rxjs";
import { ImportError, ImportStatus, ImportStatusName } from "../../models/import";
import { ImportService } from "../../services/import.service";

@Component({
  selector: "app-worker-tasks-page",
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: "./worker-tasks-page.component.html",
})
export class WorkerTasksPageComponent {
  private readonly importService = inject(ImportService);
  readonly statusOptions: Array<ImportStatusName | "all"> = [
    "all",
    "queued",
    "processing",
    "completed",
    "completed_with_errors",
    "failed",
  ];
  selectedStatus: ImportStatusName | "all" = "all";
  readonly tasks$ = timer(0, 2000).pipe(
    switchMap(() => this.importService.listTasks()),
    map((result) => result.items),
    map((tasks) =>
      this.selectedStatus === "all"
        ? tasks
        : tasks.filter((task) => task.status === this.selectedStatus),
    ),
    startWith([] as ImportStatus[]),
    catchError(() => of([] as ImportStatus[])),
  );

  statusLabel(status: ImportStatusName): string {
    return {
      queued: "В очереди",
      processing: "В работе",
      completed: "Завершено",
      completed_with_errors: "Завершено с ошибками",
      failed: "Ошибка",
    }[status];
  }

  statusClass(status: ImportStatusName): string {
    return {
      queued: "text-bg-secondary",
      processing: "text-bg-primary",
      completed: "text-bg-success",
      completed_with_errors: "text-bg-warning",
      failed: "text-bg-danger",
    }[status];
  }

  progress(task: ImportStatus): number {
    return task.total ? Math.round((task.processed / task.total) * 100) : 0;
  }

  hasErrors(task: ImportStatus): boolean {
    return this.parseErrors(task).length > 0;
  }

  parseErrors(task: ImportStatus): ImportError[] {
    if (typeof task.errors !== "string") return task.errors;

    try {
      const errors = JSON.parse(task.errors || "[]");
      return Array.isArray(errors) ? errors : [];
    } catch {
      return [{ row: 0, message: "Не удалось прочитать отчёт об ошибках" }];
    }
  }
}