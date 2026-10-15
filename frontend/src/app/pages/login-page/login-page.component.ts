import { CommonModule } from "@angular/common";
import { Component, inject } from "@angular/core";
import { FormsModule } from "@angular/forms";
import { ActivatedRoute, Router } from "@angular/router";
import { AuthService } from "../../services/auth.service";

@Component({
  selector: "app-login-page",
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: "./login-page.component.html",
})
export class LoginPageComponent {
  private readonly authService = inject(AuthService);
  private readonly router = inject(Router);
  private readonly route = inject(ActivatedRoute);

  username = "admin";
  password = "admin";
  loading = false;
  error = "";
  sessionExpired = this.route.snapshot.queryParamMap.get("reason") === "session-expired";

  submit(): void {
    if (!this.username || !this.password || this.loading) return;

    this.loading = true;
    this.error = "";
    this.authService.login(this.username, this.password).subscribe({
      next: () => void this.router.navigate(["/products"]),
      error: (error: { status?: number }) => {
        this.loading = false;
        this.error = error.status === 401
          ? "Неверный логин или пароль"
          : "Не удалось выполнить вход. Попробуйте еще раз";
      },
    });
  }
}
