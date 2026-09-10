import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

export interface ApiResponse<T> {
  status: 'sucesso' | 'erro';
  mensagem?: string;
  dados?: T;
}

export interface Produto {
  id: number;
  codigo_barras: string;
  nome: string;
  marca: string | null;
  categoria: string | null;
  peso: string | null;
}

export interface Lista {
  id: number;
  usuario_id: number;
  nome: string;
}

@Injectable({ providedIn: 'root' })
export class ApiService {
  private readonly baseUrl = 'http://localhost:8080';

  constructor(private readonly http: HttpClient) {}

  criarLista(usuarioId: number, nome: string): Observable<ApiResponse<Lista>> {
    return this.http.post<ApiResponse<Lista>>(`${this.baseUrl}/listas`, {
      usuario_id: usuarioId,
      nome
    });
  }

  buscarProduto(codigoBarras: string): Observable<ApiResponse<Produto>> {
    return this.http.get<ApiResponse<Produto>>(`${this.baseUrl}/produtos/${encodeURIComponent(codigoBarras)}`);
  }

  adicionarItem(listaId: number, produtoId: number, quantidade = 1): Observable<ApiResponse<{ id: number }>> {
    return this.http.post<ApiResponse<{ id: number }>>(`${this.baseUrl}/listas/${listaId}/itens`, {
      produto_id: produtoId,
      quantidade
    });
  }
}
