<?php

namespace Tests\Unit;

use App\Services\RecommendationService;
use PHPUnit\Framework\TestCase;

class RecommendationServiceTest extends TestCase
{
    private RecommendationService $servicio;

    protected function setUp(): void
    {
        $this->servicio = new RecommendationService();
    }

    // Datos base: un mes con ingresos y gastos moderados, sin presupuestos
    private function datos(array $cambios = []): array
    {
        return array_merge([
            'mes'             => 'Noviembre 2026',
            'ingresos'        => 2800000.0,
            'gastos'          => 1555000.0,
            'gastosMesPasado' => 0.0,
            'categorias'      => ['Alimentacion' => 620000.0, 'Transporte' => 350000.0, 'Vivienda' => 300000.0],
            'presupuestos'    => [],
            'hayMovimientos'  => true,
        ], $cambios);
    }

    private function presupuesto(float $gastado, float $limite = 1000000.0): array
    {
        return ['nombre' => 'Alimentacion', 'limite' => $limite, 'gastado' => $gastado, 'pct' => ($gastado / $limite) * 100];
    }

    private function niveles(array $recs): array
    {
        return array_column($recs, 'nivel');
    }

    private function titulos(array $recs): array
    {
        return array_column($recs, 'titulo');
    }

    public function test_sin_movimientos_pide_empezar_a_registrar(): void
    {
        $recs = $this->servicio->generate($this->datos(['hayMovimientos' => false]));

        $this->assertCount(1, $recs);
        $this->assertSame('info', $recs[0]['nivel']);
        $this->assertSame('Empieza a registrar', $recs[0]['titulo']);
    }

    public function test_presupuesto_al_79_por_ciento_no_genera_alerta(): void
    {
        $recs = $this->servicio->generate($this->datos(['presupuestos' => [$this->presupuesto(790000)]]));

        $this->assertNotContains('Cerca del limite', $this->titulos($recs));
        $this->assertNotContains('Presupuesto superado', $this->titulos($recs));
    }

    public function test_presupuesto_al_85_por_ciento_genera_advertencia(): void
    {
        $recs = $this->servicio->generate($this->datos(['presupuestos' => [$this->presupuesto(850000)]]));

        $this->assertContains('Cerca del limite', $this->titulos($recs));
        $this->assertSame('warning', $recs[array_search('Cerca del limite', $this->titulos($recs))]['nivel']);
    }

    public function test_presupuesto_al_112_por_ciento_avisa_exceso(): void
    {
        $recs = $this->servicio->generate($this->datos(['presupuestos' => [$this->presupuesto(1120000)]]));

        $i = array_search('Presupuesto superado', $this->titulos($recs));
        $this->assertNotFalse($i);
        $this->assertSame('danger', $recs[$i]['nivel']);
        $this->assertStringContainsString('Alimentacion', $recs[$i]['mensaje']);
    }

    public function test_gastos_mayores_a_ingresos_es_alerta_critica(): void
    {
        $recs = $this->servicio->generate($this->datos(['ingresos' => 1000000.0, 'gastos' => 1500000.0]));

        $this->assertContains('Gastas mas de lo que ganas', $this->titulos($recs));
    }

    public function test_gastos_sin_ingresos_pide_registrar_ingresos(): void
    {
        $recs = $this->servicio->generate($this->datos(['ingresos' => 0.0, 'gastos' => 500000.0]));

        $this->assertContains('Falta registrar ingresos', $this->titulos($recs));
    }

    public function test_ahorro_del_20_por_ciento_o_mas_es_buena_noticia(): void
    {
        $recs = $this->servicio->generate($this->datos(['ingresos' => 2000000.0, 'gastos' => 1500000.0]));

        $this->assertContains('Buen ritmo de ahorro', $this->titulos($recs));
    }

    public function test_ahorro_menor_al_20_por_ciento_sugiere_ahorrar_mas(): void
    {
        $recs = $this->servicio->generate($this->datos(['ingresos' => 2000000.0, 'gastos' => 1800000.0]));

        $this->assertContains('Puedes ahorrar mas', $this->titulos($recs));
    }

    public function test_categoria_con_40_por_ciento_o_mas_se_marca_como_concentrada(): void
    {
        $recs = $this->servicio->generate($this->datos(['categorias' => ['Alimentacion' => 800000.0, 'Transporte' => 200000.0], 'gastos' => 1000000.0]));

        $i = array_search('Gasto concentrado', $this->titulos($recs));
        $this->assertNotFalse($i);
        $this->assertStringContainsString('Alimentacion', $recs[$i]['mensaje']);
        $this->assertStringContainsString('80%', $recs[$i]['mensaje']);
    }

    public function test_gastos_que_suben_20_por_ciento_vs_mes_pasado_generan_advertencia(): void
    {
        $recs = $this->servicio->generate($this->datos(['gastos' => 1200000.0, 'gastosMesPasado' => 1000000.0]));

        $this->assertContains('Tus gastos suben', $this->titulos($recs));
    }

    public function test_gastos_que_bajan_10_por_ciento_vs_mes_pasado_son_buena_noticia(): void
    {
        $recs = $this->servicio->generate($this->datos(['gastos' => 900000.0, 'gastosMesPasado' => 1000000.0]));

        $this->assertContains('Estas gastando menos', $this->titulos($recs));
    }

    public function test_las_recomendaciones_se_ordenan_de_lo_mas_urgente_a_lo_menos(): void
    {
        $recs = $this->servicio->generate($this->datos([
            'ingresos'     => 1000000.0,
            'gastos'       => 1500000.0,
            'presupuestos' => [$this->presupuesto(850000)],
        ]));

        $orden = ['danger' => 0, 'warning' => 1, 'info' => 2, 'success' => 3];
        $valores = array_map(fn ($n) => $orden[$n], $this->niveles($recs));
        $ordenados = $valores;
        sort($ordenados);

        $this->assertSame($ordenados, $valores);
        $this->assertSame('danger', $recs[0]['nivel']);
    }

    public function test_nunca_devuelve_mas_de_seis_recomendaciones(): void
    {
        $presupuestos = [];
        for ($i = 0; $i < 10; $i++) {
            $presupuestos[] = $this->presupuesto(1200000);
        }

        $recs = $this->servicio->generate($this->datos(['presupuestos' => $presupuestos]));

        $this->assertLessThanOrEqual(6, count($recs));
    }
}
