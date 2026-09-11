import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { IonContent, IonButton } from '@ionic/angular';
import { HeroCarouselComponent } from '../../components/hero-carousel/hero-carousel.component';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule, IonContent, IonButton, HeroCarouselComponent, RouterLink],
  templateUrl: './home.page.html',
  styleUrls: ['./home.page.scss'],
})
export class HomePage {
  constructor(private router: Router) {}

  start(): void {
    console.log('BOTÃO CLICADO - Tentando navegar para /lista...');
    this.router.navigateByUrl('/lista');
  }
}
