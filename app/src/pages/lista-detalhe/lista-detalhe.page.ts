import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import {
  IonButton, IonContent, IonHeader, IonTitle, IonToolbar, IonList, IonItem, IonLabel,
  IonButtons, IonIcon, IonSpinner, IonListHeader
} from '@ionic/angular';
import { ActivatedRoute } from '@angular/router';
import { NavController } from '@ionic/angular';
import { ApiService, Lista } from '../../services/api.service';
import { finalize } from 'rxjs';

@Component({
  selector: 'app-lista-detalhe',
  standalone: true,
  imports: [
    CommonModule, IonButton, IonContent, IonHeader, IonTitle, IonToolbar, IonList, IonItem, IonLabel,
    IonButtons, IonIcon, IonSpinner, IonListHeader
  ],
  template: `
    <ion-header>
      <ion-toolbar color="primary">
        <ion-buttons slot="start">
          <ion-button (click)="voltar()">
            <ion-icon name="arrow-back"></ion-icon>
          </ion-button>
        </ion-buttons>
        <ion-title>{{ lista?.nome || 'Detalhes da Lista' }}</ion-title>
      </ion-toolbar>
    </ion-header>
    <ion-content class="ion-padding">
      <div *ngIf="carregando" class="ion-text-center">
        <ion-spinner></ion-spinner>
      </div>
      <div *ngIf="!carregando && lista">
        <p>ID da Lista: {{ lista.id }}</p>
        <p>Usuário: {{ lista.usuario_id }}</p>
        <ion-list>
          <ion-list-header>Produtos</ion-list-header>
          <ion-item>
            <ion-label>
              <p>A implementação dos itens da lista será feita a seguir.</p>
            </ion-label>
          </ion-item>
        </ion-list>
      </div>
    </ion-content>
  `
})
export class ListaDetalhePage implements OnInit {
  lista?: Lista;
  carregando = false;

  constructor(
    private readonly route: ActivatedRoute,
    private readonly navCtrl: NavController,
    private readonly api: ApiService
  ) {}

  ngOnInit(): void {
    const id = this.route.snapshot.paramMap.get('id');
    if (id) {
      this.carregarLista(parseInt(id));
    }
  }

  carregarLista(id: number): void {
    this.carregando = true;
    // Note: ApiService needs a method to get a single list by ID
    this.carregando = false;
  }

  voltar(): void {
    this.navCtrl.navigateBack('/lista');
  }
}
