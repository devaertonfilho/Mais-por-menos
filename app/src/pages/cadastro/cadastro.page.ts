import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import {
  IonButton, IonContent, IonHeader, IonInput, IonItem, IonLabel,
  IonTitle, IonToolbar, IonButtons, IonBackButton, IonNote
} from '@ionic/angular';
import { finalize } from 'rxjs';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-cadastro',
  standalone: true,
  imports: [
    CommonModule, ReactiveFormsModule, IonButton, IonContent, IonHeader, IonInput,
    IonItem, IonLabel, IonTitle, IonToolbar, IonButtons, IonBackButton, IonNote
  ],
  templateUrl: './cadastro.page.html',
  styleUrls: ['./cadastro.page.scss']
})
export class CadastroPage {
  readonly form = this.formBuilder.nonNullable.group({
    nome: ['', [Validators.required]],
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

  async cadastrar(): void {
    if (this.form.invalid) {
      this.mensagem = 'Por favor, preencha todos os campos corretamente.';
      return;
    }

    const { nome, email, senha } = this.form.getRawValue();
    this.carregando = true;
    this.mensagem = '';

    this.authService.register(nome, email, senha).pipe(
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
}
