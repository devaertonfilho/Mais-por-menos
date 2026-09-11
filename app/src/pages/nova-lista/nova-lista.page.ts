import { CommonModule } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import {
  IonButton, IonContent, IonHeader, IonInput, IonItem, IonLabel,
  IonList, IonNote, IonSpinner, IonTitle, IonToolbar, IonButtons, IonBackButton
} from '@ionic/angular';
import { concatMap, finalize, map } from 'rxjs';
import { ApiResponse, ApiService, Lista, Produto } from '../../services/api.service';

@Component({
  selector: 'app-nova-lista',
  standalone: true,
  imports: [
    CommonModule, ReactiveFormsModule, IonButton, IonContent, IonHeader, IonInput,
    IonItem, IonLabel, IonList, IonNote, IonSpinner, IonTitle, IonToolbar, IonButtons, IonBackButton
  ],
  templateUrl: './nova-lista.page.html',
  styleUrls: ['./nova-lista.page.scss']
})
export class NovaListaPage {
  readonly listaForm = this.formBuilder.nonNullable.group({
    nome: ['', [Validators.required]]
  });
  readonly codigoBarrasControl = this.formBuilder.nonNullable.control('', Validators.required);

  lista?: Lista;
  itens: Produto[] = [];
  carregando = false;
  mensagem = '';

  constructor(
    private readonly formBuilder: FormBuilder,
    private readonly api: ApiService,
    private readonly router: Router
  ) {}

  criarLista(): void {
    if (this.listaForm.invalid) {
      this.mensagem = 'Por favor, informe o nome da lista.';
      return;
    }

    const { nome } = this.listaForm.getRawValue();
    this.carregando = true;
    this.mensagem = '';

    this.api.criarLista(nome).pipe(
      finalize(() => this.carregando = false)
    ).subscribe({
      next: (response) => {
        this.lista = response.dados;
        this.itens = [];
        this.mensagem = response.mensagem ?? 'Lista criada com sucesso!';

        // Navega para a home após 1.5s para o usuário ver a mensagem de sucesso
        setTimeout(() => this.router.navigate(['/home']), 1500);
      },
      error: (error: HttpErrorResponse | Error) => this.mensagem = this.errorMessage(error)
    });
  }

  lerCodigoBarras(): void {
    const codigoBarras = this.codigoBarrasControl.value.trim();
    if (!this.lista?.id) {
      this.mensagem = 'Crie uma lista antes de adicionar produtos.';
      return;
    }
    if (!codigoBarras) {
      this.mensagem = 'Informe um código de barras.';
      return;
    }

    this.carregando = true;
    this.mensagem = '';
    this.api.buscarProduto(codigoBarras).pipe(
      concatMap((response) => {
        if (!response.dados) {
          throw new Error(response.mensagem ?? 'Produto não encontrado.');
        }
        const produto = response.dados;
        return this.api.adicionarItem(this.lista!.id, produto.id).pipe(map((itemResponse) => ({ produto, itemResponse })));
      }),
      finalize(() => this.carregando = false)
    ).subscribe({
      next: ({ produto, itemResponse }) => {
        this.itens = [...this.itens, produto];
        this.codigoBarrasControl.reset();
        this.mensagem = itemResponse.mensagem ?? 'Produto adicionado à lista.';
      },
      error: (error: HttpErrorResponse | Error) => this.mensagem = this.errorMessage(error)
    });
  }

  private errorMessage(error: HttpErrorResponse | Error): string {
    if (error instanceof HttpErrorResponse) {
      return (error.error as ApiResponse<unknown>)?.mensagem ?? 'Não foi possível comunicar com a API.';
    }
    return error.message;
  }
}
