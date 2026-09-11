import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NavController, ToastController, IonContent, IonHeader, IonTitle, IonToolbar, IonButton, IonIcon } from '@ionic/angular';
import { BarcodeScanner } from '@capacitor-mlkit/barcode-scanning';
import { ApiService, Produto, ApiResponse } from '../../services/api.service';

@Component({
  selector: 'app-scanner',
  templateUrl: './scanner.page.html',
  styleUrls: ['./scanner.page.css'],
  standalone: true,
  imports: [CommonModule, IonContent, IonHeader, IonTitle, IonToolbar, IonButton, IonIcon],
})
export class ScannerPage implements OnInit, OnDestroy {
  public isScanning = false;

  constructor(
    private readonly navCtrl: NavController,
    private readonly apiService: ApiService,
    private readonly toastCtrl: ToastController
  ) {}

  async ngOnInit() {
    await this.checkPermissions();
    this.startScan();
  }

  async ngOnDestroy() {
    await this.stopScan();
  }

  private async checkPermissions() {
    const { camera } = await BarcodeScanner.checkPermissions();
    if (camera !== 'granted') {
      const { camera: status } = await BarcodeScanner.requestPermissions();
      if (status !== 'granted') {
        this.showToast('Permissão de câmera necessária para escanear produtos.');
        this.navCtrl.navigateBack('/nova-lista');
      }
    }
  }

  async startScan() {
    try {
      await BarcodeScanner.startScan();
      this.isScanning = true;
    } catch (error) {
      console.error('Erro ao iniciar scanner:', error);
      this.showToast('Erro ao iniciar a câmera.');
    }
  }

  async stopScan() {
    try {
      await BarcodeScanner.stopScan();
      this.isScanning = false;
    } catch (error) {
      console.error('Erro ao parar scanner:', error);
    }
  }

  async handleScanResult(result: string) {
    await this.stopScan();

    try {
      this.apiService.buscarProduto(result).subscribe({
        next: (response: ApiResponse<Produto>) => {
          if (response.status === 'sucesso' && response.dados) {
            const produto = response.dados;
            this.navCtrl.navigateBack('/nova-lista', {
              queryParams: { produtoId: produto.id, nome: produto.nome }
            });
          } else {
            this.showToast('Produto não encontrado na base de dados.');
            this.startScan();
          }
        },
        error: (err: any) => {
          console.error('Erro na API:', err);
          this.showToast('Erro ao consultar o produto.');
          this.startScan();
        }
      });
    } catch (error) {
      this.showToast('Erro ao processar o código de barras.');
      this.startScan();
    }
  }

  async cancel() {
    await this.stopScan();
    this.navCtrl.navigateBack('/nova-lista');
  }

  private async showToast(message: string) {
    const toast = await this.toastCtrl.create({
      message,
      duration: 3000,
      position: 'bottom'
    });
    await toast.present();
  }
}
