import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import {
  IonButton, IonContent, IonHeader, IonItem, IonLabel,
  IonList, IonNote, IonSpinner, IonTitle, IonToolbar, IonIcon
} from '@ionic/angular';
import { NavController } from '@ionic/angular';
import { ApiResponse, ApiService, Lista } from '../../services/api.service';
import { finalize, timeout, catchError } from 'rxjs';
import { of } from 'rxjs';

@Component({
  selector: 'app-lista',
  standalone: true,
  imports: [
    CommonModule, IonButton, IonContent, IonHeader, IonItem,
    IonLabel, IonList, IonNote, IonSpinner, IonTitle, IonToolbar, IonIcon
  ],
  templateUrl: './lista.page.html'
})
export class ListaPage implements OnInit {
  listas: Lista[] = [];
  carregando = false;
  mensagem = '';

  constructor(
    public readonly api: ApiService,
    public readonly navCtrl: NavController
  ) {
    console.log('DEBUG: ListaPage Constructor chamado!');
  }

  ngOnInit(): void {
    console.log('DEBUG: ListaPage ngOnInit chamado!');
    this.carregarListas();
  }

  carregarListas(): void {
    console.log('DEBUG: carregarListas() iniciado...');
    this.carregando = true;
    this.mensagem = '';

    // Fail-safe: Força o fim do carregamento após 10 segundos, não importa o que aconteça
    const failSafeTimer = setTimeout(() => {
      if (this.carregando) {
        console.warn('DEBUG: Fail-safe ativado! O servidor demorou demais.');
        this.carregando = false;
        this.mensagem = 'O servidor não respondeu a tempo. Verifique sua conexão.';
      }
    }, 10000);

    this.api.buscarListas(1).pipe(
      timeout(5000),
      catchError(err => {
        console.error('DEBUG: Erro de rede ou Timeout:', err);
        return of({ status: 'erro', mensagem: 'Tempo de resposta esgotado ou erro de rede.' } as ApiResponse<Lista[]>);
      }),
      finalize(() => {
        console.log('DEBUG: carregarListas() finalizado.');
        this.carregando = false;
        clearTimeout(failSafeTimer);
      })
    ).subscribe({
      next: (response) => {
        console.log('DEBUG: Resposta recebida do servidor:', response);
        this.listas = response.dados ?? [];
        if (this.listas.length === 0) {
          this.mensagem = 'Nenhuma lista encontrada.';
        }
      },
      error: (error) => {
        console.error('DEBUG: Erro crítico ao carregar listas:', error);
        this.mensagem = 'Erro crítico ao conectar com o servidor.';
      }
    });
  }

  criarNovaLista(): void {
    this.navCtrl.navigateForward('/nova-lista');
  }
}
