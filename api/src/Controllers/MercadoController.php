<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use PDOException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class MercadoController extends ApiController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function proximos(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getQueryParams();

        $lat = filter_var($params['lat'] ?? null, FILTER_VALIDATE_FLOAT);
        $lng = filter_var($params['lng'] ?? null, FILTER_VALIDATE_FLOAT);
        $raio = filter_var($params['raio'] ?? 5, FILTER_VALIDATE_FLOAT);
        $limite = filter_var($params['limite'] ?? 10, FILTER_VALIDATE_INT);

        if ($lat === false || $lng === false) {
            return $this->error($response, 'Latitude e Longitude são obrigatórios e devem ser valores numéricos.', 400);
        }

        if ($raio === false || $raio <= 0) {
            $raio = 5.0;
        }

        if ($limite === false || $limite <= 0) {
            $limite = 10;
        }

        try {
            // Fórmula de Haversine para calcular distância em KM
            $sql = "
                SELECT id, nome, endereco, cidade, latitude, longitude,
                (6371 * acos(
                    cos(radians(:lat)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(:lng)) +
                    sin(radians(:lat)) * sin(radians(latitude))
                )) AS distancia_km
                FROM mercados
                HAVING distancia_km <= :raio
                ORDER BY distancia_km ASC
                LIMIT :limite
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':lat', $lat);
            $stmt->bindValue(':lng', $lng);
            $stmt->bindValue(':raio', $raio);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();

            $mercados = $stmt->fetchAll();

            // Formata a distância para 2 casas decimais
            $resultados = array_map(function ($mercado) {
                $mercado['distancia_km'] = round((float)$mercado['distancia_km'], 2);
                return $mercado;
            }, $mercados);

            return $this->json($response, [
                'status' => 'sucesso',
                'dados' => $resultados
            ]);
        } catch (PDOException $e) {
            return $this->error($response, 'Erro ao buscar mercados próximos: ' . $e->getMessage(), 500);
        }
    }
}
