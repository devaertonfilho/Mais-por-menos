import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import {
  IonContent, IonHeader, IonTitle, IonToolbar,
  IonButton, IonList, IonItem, IonLabel,
  IonIcon, IonButtons, IonBackButton, IonFab, IonFabButton, IonSpinner
} from '@ionic/angular';
import { ApiService, Lista } from '../../services/api.service';
import { AuthService } from '../../services/auth.service';
import { finalize } from 'rxjs';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [
    CommonModule, RouterLink,
    IonContent, IonHeader, IonTitle, IonToolbar,
    IonButton, IonList, IonItem, IonLabel,
    IonIcon, IonButtons, IonBackButton, IonFab, IonFabButton, IonSpinner
  ],
  templateUrl: './home.page.html',
  styleUrls: ['./home.page.scss'],
})
export class HomePage implements OnInit {
  listas: Lista[] = [];
  carregando = true;
  mensagem = '';

  constructor(
    private readonly api: ApiService,
    private readonly auth: AuthService,
    private readonly router: Router
  ) {}

  async ngOnInit() {
    await this.carregarListas();
  }

  async carregarListas() {
    this.carregando = true;
    try {
      const user = await this.auth.getUser();
      if (!user) {
        this.mensagem = 'Usuário não autenticado.';
        this.router.navigate(['/login']);
        return;
      }

      this.api.listarMinhasListas().pipe(
        finalize(() => this.carregando = false)
      ).subscribe({
        next: (response) => {
          if (response.status === 'sucesso' && response.dados) {
            this.listas = response.dados;
          } else {
            this.mensagem = response.mensagem ?? 'Erro ao carregar listas.';
          }
        },
        error: (err) => {
          console.error('Erro ao buscar listas:', err);
          this.mensagem = 'Erro de conexão com o servidor.';
        }
      });
    } catch (error) {
      console.error('Erro ao obter usuário:', error);
      this.carregando = false;
    }
  }

  irParaNovaLista() {
    this.router.navigate(['/nova-lista']);
  }

  abrirLista(listaId: number) {
    this.router.navigate(['/lista-detalhe', listaId]);
  }
}
