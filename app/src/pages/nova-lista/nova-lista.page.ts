import { CommonModule } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import {
  IonButton, IonContent, IonHeader, IonInput, IonItem, IonLabel,
  IonList, IonNote, IonSpinner, IonTitle, IonToolbar, IonIcon
} from '@ionic/angular';
import { ActivatedRoute } from '@angular/router';
import { NavController } from '@ionic/angular';
import { concatMap, finalize, map } from 'rxjs';
import { ApiResponse, ApiService, Lista, Produto } from '../../services/api.service';

@Component({
  selector: 'app-nova-lista',
  imports: [
    CommonModule, ReactiveFormsModule, IonButton, IonContent, IonHeader, IonInput,
    IonItem, IonLabel, IonList, IonNote, IonSpinner, IonTitle, IonToolbar, IonIcon
  ],
  templateUrl: './nova-lista.page.html'
})
export class NovaListaPage implements OnInit {
  readonly listaForm = this.formBuilder.nonNullable.group({
    usuarioId: [1, [Validators.required, Validators.min(1)]],
    nome: ['', Validators.required]
  });
  readonly codigoBarrasControl = this.formBuilder.nonNullable.control('', Validators.required);

  lista?: Lista;
  itens: Produto[] = [];
  carregando = false;
  mensagem = '';

  constructor(
    private readonly formBuilder: FormBuilder,
    private readonly api: ApiService,
    private readonly navCtrl: NavController,
    private readonly route: ActivatedRoute
  ) {}

  ngOnInit(): void {
    this.route.queryParams.subscribe(params => {
      if (params['produtoId']) {
        this.adicionarProdutoEscaneado(params['produtoId'], params['nome']);
      }
    });
  }

  abrirScanner(): void {
    this.navCtrl.navigateForward('/scanner');
  }

  private adicionarProdutoEscaneado(produtoId: string, nome: string): void {
    if (!this.lista?.id) {
      this.mensagem = 'Crie uma lista antes de adicionar produtos.';
      return;
    }

    this.carregando = true;
    this.api.adicionarItem(this.lista.id, parseInt(produtoId)).pipe(
      finalize(() => this.carregando = false)
    ).subscribe({
      next: (response) => {
        this.itens = [...this.itens, { id: parseInt(produtoId), nome, codigo_barras: '', marca: null, categoria: null, peso: null }];
        this.mensagem = response.mensagem ?? 'Produto escaneado adicionado.';
      },
      error: (error: HttpErrorResponse | Error) => this.mensagem = this.errorMessage(error)
    });
  }

  criarLista(): void {
    if (this.listaForm.invalid) {
      this.mensagem = 'Informe o ID do usuário e o nome da lista.';
      return;
    }

    const { usuarioId, nome } = this.listaForm.getRawValue();
    this.carregando = true;
    this.mensagem = '';

    this.api.criarLista(usuarioId, nome).pipe(finalize(() => this.carregando = false)).subscribe({
      next: (response) => {
        this.lista = response.dados;
        this.itens = [];
        this.mensagem = response.mensagem ?? 'Lista criada.';
      },
      error: (error: HttpErrorResponse) => this.mensagem = this.errorMessage(error)
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
