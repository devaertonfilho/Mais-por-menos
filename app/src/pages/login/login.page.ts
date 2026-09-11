import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { ReactiveFormsModule, Validators } from '@angular/forms';
import { FormBuilder } from '@angular/forms';
import {
  IonButton, IonContent, IonHeader, IonInput, IonItem, IonLabel,
  IonTitle, IonToolbar, IonButtons, IonBackButton, IonNote
} from '@ionic/angular';
import { finalize } from 'rxjs';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [
    CommonModule, ReactiveFormsModule, IonButton, IonContent, IonHeader, IonInput,
    IonItem, IonLabel, IonTitle, IonToolbar, IonButtons, IonBackButton, IonNote
  ],
  templateUrl: './login.page.html',
  styleUrls: ['./login.page.scss']
})
export class LoginPage {
  readonly form = this.formBuilder.nonNullable.group({
    email: ['', [Validators.required, Validators.email]],
    senha: ['', [Validators.required]]
  });

  carregando = false;
  mensagem = '';

  constructor(
    private readonly formBuilder: FormBuilder,
    private readonly authService: AuthService,
    public readonly router: Router
  ) {}

  async login(): void {
    if (this.form.invalid) {
      this.mensagem = 'Por favor, informe email e senha corretamente.';
      return;
    }

    const { email, senha } = this.form.getRawValue();
    this.carregando = true;
    this.mensagem = '';

    this.authService.login(email, senha).pipe(
      finalize(() => this.carregando = false)
    ).subscribe({
      next: async (response) => {
        const { token, usuario } = response.dados;
        await this.authService.setToken(token, usuario);

        this.mensagem = 'Login realizado com sucesso!';
        setTimeout(() => this.router.navigate(['/nova-lista']), 1500);
      },
      error: (error) => {
        this.mensagem = 'Email ou senha incorretos.';
      }
    });
  }
}
