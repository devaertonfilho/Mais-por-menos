import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { IonContent, IonButton } from '@ionic/angular';
import { HeroCarouselComponent } from '../../components/hero-carousel/hero-carousel.component';

@Component({
  selector: 'app-onboarding',
  standalone: true,
  imports: [CommonModule, IonContent, IonButton, HeroCarouselComponent, RouterLink],
  templateUrl: './onboarding.page.html',
  styleUrls: ['./onboarding.page.scss'],
})
export class OnboardingPage {
  constructor(private router: Router) {}

  start(): void {
    console.log('Botão "Começar Agora" clicado. Navegando para cadastro...');
    this.router.navigate(['/cadastro']);
  }
}
