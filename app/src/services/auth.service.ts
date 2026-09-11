import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Preferences } from '@capacitor/preferences';
import { Observable, from, of } from 'rxjs';
import { map, switchMap } from 'rxjs/operators';
import { ApiResponse } from './api.service';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly TOKEN_KEY = 'auth_token';
  private readonly USER_KEY = 'auth_user';
  private readonly baseUrl = 'http://localhost:8000'; // Ajustado para o servidor local

  constructor(private readonly http: HttpClient) {}

  async setToken(token: string, user: any): Promise<void> {
    await Preferences.set({ key: this.TOKEN_KEY, value: token });
    await Preferences.set({ key: this.USER_KEY, value: JSON.stringify(user) });
  }

  async getToken(): Promise<string | null> {
    const { value } = await Preferences.get({ key: this.TOKEN_KEY });
    return value;
  }

  async getUser(): Promise<any | null> {
    const { value } = await Preferences.get({ key: this.USER_KEY });
    return value ? JSON.parse(value) : null;
  }

  async logout(): Promise<void> {
    await Preferences.remove({ key: this.TOKEN_KEY });
    await Preferences.remove({ key: this.USER_KEY });
  }

  register(nome: string, email: string, senha: string): Observable<ApiResponse<any>> {
    return this.http.post<ApiResponse<any>>(`${this.baseUrl}/usuarios`, { nome, email, senha });
  }

  login(email: string, senha: string): Observable<ApiResponse<{ token: string, usuario: any }>> {
    return this.http.post<ApiResponse<{ token: string, usuario: any }>>(`${this.baseUrl}/login`, { email, senha });
  }

  async isAuthenticated(): Promise<boolean> {
    return !!(await this.getToken());
  }
}
