import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import {
  IonButton, IonContent, IonHeader, IonItem, IonLabel,
  IonList, IonNote, IonSpinner, IonTitle, IonToolbar, IonIcon
} from '@ionic/angular';
import { NavController } from '@ionic/angular';
import { ApiResponse, ApiService, Lista } from '../../services/api.service';
import { finalize } from 'rxjs';

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
  ) {}

  ngOnInit(): void {
    this.carregarListas();
  }

  carregarListas(): void {
    this.carregando = true;
    this.mensagem = '';
    // Usando usuarioId = 1 como padrão, assim como em NovaListaPage
    this.api.buscarListas(1).pipe(
      finalize(() => this.carregando = false)
    ).subscribe({
      next: (response) => {
        this.listas = response.dados ?? [];
        if (this.listas.length === 0) {
          this.mensagem = 'Nenhuma lista encontrada.';
        }
      },
      error: (error) => this.mensagem = 'Não foi possível carregar as listas.'
    });
  }

  criarNovaLista(): void {
    this.navCtrl.navigateForward('/nova-lista');
  }
}
