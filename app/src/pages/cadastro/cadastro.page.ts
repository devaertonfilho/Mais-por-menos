import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import {
  IonButton, IonContent, IonInput, IonItem,
  IonSpinner, IonIcon
} from '@ionic/angular';
import { finalize } from 'rxjs';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-cadastro',
  standalone: true,
  imports: [
    CommonModule, ReactiveFormsModule, IonButton, IonContent, IonInput,
    IonItem, IonSpinner, IonIcon
  ],
  templateUrl: './cadastro.page.html',
  styleUrls: ['./cadastro.page.scss']
})
export class CadastroPage {
  readonly form = this.formBuilder.nonNullable.group({
    email: ['', [Validators.required, Validators.email]],
    senha: ['', [Validators.required, Validators.minLength(6)]]
  });

  carregando = false;
  mensagem = '';

  constructor(
    private readonly formBuilder: FormBuilder,
    private readonly authService: AuthService,
    public readonly router: Router
  ) {}

  async cadastrar(): Promise<void> {
    if (this.form.invalid) {
      this.mensagem = 'Por favor, preencha todos os campos corretamente.';
      return;
    }

    const { email, senha } = this.form.getRawValue();
    this.carregando = true;
    this.mensagem = '';

    // Como o backend pede 'nome', simulamos como 'Novo Usuário'.
    this.authService.register('Novo Usuário', email, senha).pipe(
      finalize(() => this.carregando = false)
    ).subscribe({
      next: (response) => {
        this.mensagem = response.mensagem ?? 'Cadastro realizado com sucesso!';
        setTimeout(() => this.router.navigate(['/login']), 1500);
      },
      error: (error) => {
        this.mensagem = 'Erro ao cadastrar. Verifique se o email já existe.';
      }
    });
  }

  socialLogin(provider: string): void {
    console.log(`Social login via ${provider} clicado (Placeholder)`);
  }
}
