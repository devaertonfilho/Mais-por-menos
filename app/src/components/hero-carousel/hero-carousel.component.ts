import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';

interface Slide {
  id: string;
  title: string;
  subtitle: string;
  gradient: string;
  badgeGradient: string;
  icon: string;
  floatIcon: string;
  accentColor: string;
}

@Component({
  selector: 'app-hero-carousel',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './hero-carousel.component.html',
  styleUrls: ['./hero-carousel.component.scss']
})
export class HeroCarouselComponent implements OnInit, OnDestroy {
  readonly SLIDE_DURATION = 4000;

  slides: Slide[] = [
    {
      id: 'scan',
      title: 'Escaneie e economize',
      subtitle: 'Aponte a câmera pro código de barras e veja o preço na hora.',
      gradient: 'linear-gradient(160deg, #1B4332 0%, #2D6A4F 60%, #40916C 100%)',
      badgeGradient: 'radial-gradient(circle at 35% 30%, #B7E4C7, #52B788 55%, #2D6A4F 100%)',
      icon: 'scan-outline',
      floatIcon: 'checkmark-circle-outline',
      accentColor: '#2D6A4F'
    },
    {
      id: 'compare',
      title: 'Compare preços em tempo real',
      subtitle: 'A gente te mostra onde essa compra sai mais barata.',
      gradient: 'linear-gradient(160deg, #7A5C00 0%, #B08900 55%, #E8B400 100%)',
      badgeGradient: 'radial-gradient(circle at 35% 30%, #FFE8A3, #F4A300 55%, #B08900 100%)',
      icon: 'pricetag-outline',
      floatIcon: 'sparkles-outline',
      accentColor: '#B08900'
    },
    {
      id: 'ai',
      title: 'A IA monta sua lista',
      subtitle: 'Com base no que sua família consome, sem esforço nenhum.',
      gradient: 'linear-gradient(160deg, #1E3A5F 0%, #2C5282 55%, #3E7CB8 100%)',
      badgeGradient: 'radial-gradient(circle at 35% 30%, #BEE3F8, #63A9DB 55%, #2C5282 100%)',
      icon: 'sparkles-outline',
      floatIcon: 'checkmark-circle-outline',
      accentColor: '#2C5282'
    },
    {
      id: 'local',
      title: 'Preços reais da sua cidade',
      subtitle: 'Dados oficiais dos mercados perto de você, sempre atualizados.',
      gradient: 'linear-gradient(160deg, #4A2545 0%, #7A3E6E 55%, #A8548F 100%)',
      badgeGradient: 'radial-gradient(circle at 35% 30%, #F3C9E8, #C97BB0 55%, #7A3E6E 100%)',
      icon: 'cart-outline',
      floatIcon: 'location-outline',
      accentColor: '#7A3E6E'
    },
  ];

  index = 0;
  paused = false;
  progressKey = 0;
  dragX = 0;
  private timer: any;
  private startX = 0;
  private isDragging = false;
  private resumeTimeout: any;

  ngOnInit() {
    this.startTimer();
  }

  ngOnDestroy() {
    this.stopTimer();
  }

  startTimer() {
    this.stopTimer();
    this.timer = setInterval(() => {
      if (!this.paused) {
        this.goTo(this.index + 1);
      }
    }, this.SLIDE_DURATION);
  }

  stopTimer() {
    if (this.timer) clearInterval(this.timer);
  }

  goTo(next: number) {
    const total = this.slides.length;
    this.index = ((next % total) + total) % total;
    this.progressKey++;
  }

  pauseThenResume() {
    this.paused = true;
    if (this.resumeTimeout) clearTimeout(this.resumeTimeout);
    this.resumeTimeout = setTimeout(() => {
      this.paused = false;
    }, 1500);
  }

  onPointerDown(e: PointerEvent) {
    this.isDragging = true;
    this.startX = e.clientX;
    this.dragX = 0;
    this.paused = true;
    (e.currentTarget as HTMLElement).setPointerCapture(e.pointerId);
  }

  onPointerMove(e: PointerEvent) {
    if (!this.isDragging) return;
    this.dragX = e.clientX - this.startX;
  }

  onPointerUp() {
    if (!this.isDragging) return;
    this.isDragging = false;
    const threshold = 60;
    if (this.dragX < -threshold) {
      this.goTo(this.index + 1);
    } else if (this.dragX > threshold) {
      this.goTo(this.index - 1);
    } else {
      this.progressKey++;
    }
    this.dragX = 0;
    this.pauseThenResume();
  }

  get active() {
    return this.slides[this.index];
  }
}
