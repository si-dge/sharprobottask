import { Component, inject } from "@angular/core";
import { NgIf } from "@angular/common";
import { RouterLink, RouterOutlet } from "@angular/router";
import { Router } from "@angular/router";
import { AuthService } from "./services/auth.service";
@Component({
  selector: "app-root",
  standalone: true,
  imports: [NgIf, RouterLink, RouterOutlet],
  templateUrl: "./app.component.html",
})
export class AppComponent {
  readonly authService = inject(AuthService);
  private readonly router = inject(Router);

  logout(): void {
    this.authService.logout().subscribe({
      next: () => void this.router.navigate(["/login"]),
      error: () => {
        this.authService.clearLocalSession();
        void this.router.navigate(["/login"]);
      },
    });
  }
}
