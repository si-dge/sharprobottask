import { CommonModule } from "@angular/common";
import { Component, inject } from "@angular/core";
import { interval, Subscription, switchMap, takeWhile } from "rxjs";
import { ImportStatus } from "../../models/import";
import { ImportService } from "../../services/import.service";

@Component({
  selector: "app-import-page",
  standalone: true,
  imports: [CommonModule],
  templateUrl: "./import-page.component.html",
})
export class ImportPageComponent {
  private readonly importService = inject(ImportService);
  file: File | null = null;
  uploading = false;
  resetting = false;
  progress = -1;
  message = "";
  errors: Array<{ row: number; message: string }> = [];
  private poll?: Subscription;

  choose(event: Event) {
    const input = event.target as HTMLInputElement;
    this.file = input.files?.[0] ?? null;
  }

  upload() {
    if (!this.file) return;
    this.uploading = true;
    this.progress = 0;
    this.errors = [];
    this.importService.create(this.file).subscribe({
      next: (data) => {
        localStorage.setItem("lastImportId", data.importId);
        this.message = "Импорт поставлен в очередь";
        this.poll = interval(1000)
          .pipe(
            switchMap(() => this.importService.getStatus(data.importId)),
            takeWhile(
              (status) =>
                status.status === "queued" || status.status === "processing",
              true,
            ),
          )
          .subscribe((status: ImportStatus) => {
            this.progress = status.total
              ? Math.round((status.processed / status.total) * 100)
              : 10;
            if (
              status.status.startsWith("completed") ||
              status.status === "failed"
            ) {
              this.uploading = false;
              this.message = status.status;
              this.errors = this.parseImportErrors(status.errors);
            }
          });
      },
      error: () => {
        this.uploading = false;
        this.progress = -1;
        this.message = "Не удалось поставить импорт в очередь";
      },
    });
  }

  resetDatabase() {
    if (!window.confirm("Очистить базу данных и очередь импорта?")) return;

    this.resetting = true;
    this.poll?.unsubscribe();
    this.importService.reset().subscribe({
      next: (response) => {
        localStorage.removeItem("lastImportId");
        this.file = null;
        this.uploading = false;
        this.progress = -1;
        this.errors = [];
        this.message = response.message;
        this.resetting = false;
      },
      error: () => {
        this.resetting = false;
        this.message = "Не удалось очистить базу данных";
      },
    });
  }

  private parseImportErrors(
    errors: ImportStatus["errors"],
  ): Array<{ row: number; message: string }> {
    return typeof errors === "string" ? JSON.parse(errors || "[]") : errors;
  }
}