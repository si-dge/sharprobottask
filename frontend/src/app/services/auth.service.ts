import { HttpClient } from "@angular/common/http";
import { Injectable, inject } from "@angular/core";
import { Observable, tap } from "rxjs";

export interface LoginResponse {
  token: string;
  tokenType: "Bearer";
  expiresIn: number;
  user: { username: string };
}

@Injectable({ providedIn: "root" })
export class AuthService {
  private readonly http = inject(HttpClient);
  private readonly tokenKey = "authToken";

  login(username: string, password: string): Observable<LoginResponse> {
    return this.http
      .post<LoginResponse>("/api/login", { username, password })
      .pipe(tap((response) => localStorage.setItem(this.tokenKey, response.token)));
  }

  logout(): Observable<void> {
    return this.http.post<void>("/api/logout", {}).pipe(
      tap(() => localStorage.removeItem(this.tokenKey)),
    );
  }

  clearLocalSession(): void {
    localStorage.removeItem(this.tokenKey);
  }

  getToken(): string | null {
    return localStorage.getItem(this.tokenKey);
  }

  isAuthenticated(): boolean {
    return this.getToken() !== null;
  }
}
