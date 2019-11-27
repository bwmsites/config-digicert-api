<?php

namespace App\Services;

use App\Models\NFe\NFeConfig;
use Illuminate\Support\Facades\DB;
use App\Services\CertificadoService;
use Illuminate\Http\Request;

/**
 * Description of UtilService
 *
 * @author Bruno Silva
 */
class UtilService {
   public static function getNFeConfig()
   {
        //$config = NFeConfig::where('contratante', $contratante)->get();
       $config = DB::table('nfe_config')
               ->join('empresa_local', function($join) {
                   $join->on('nfe_config.empresa', '=', 'empresa_local.empresa');
               })
               ->get();
       
        if (count($config) > 0) {
            return [
                "retorno" => "ok",
                "msg" => "Registro localizado",
                "body" => $config[0]
            ];
        }
        
        return [
                "retorno" => "erro",
                "msg" => "Registro não localizado",
                "body" => null
            ];
    }

    public static function desformataCNPJ($cnpj)
    {
        return \str_replace('', '.', \str_replace('', '/', \str_replace('', '-', $cnpj)));
    }

    public static function getIDValue()
    {
        return DB::table('empresa_local')
            ->selectRaw("gera_novo_id_tabelas_f(empresa::int4,nextval('id_tabelas_seq'::text)::int4)")
            ->get()[0]->gera_novo_id_tabelas_f;
    }

    public static function storeLogoFile(Request $req)
    {
        if ($req->hasFile('logo') && $req->file('logo')->isValid()) {
            $logo = $req->file('logo');

            $realPath = $logo->getRealPath();
            $logoName = $logo->getClientOriginalName();
            $finaldir = \config_path('nfe' . DIRECTORY_SEPARATOR . 'logo');
            \logger('Gravando arquivo ' . $logoName . ' em ' . $finaldir);
            try {
                $upload = $logo->move($finaldir, $logoName);
                return $logoName;
            } catch (\Exception $e) {
                \logger('Erro ao mover arquivo para o diretório padrão. Detalhe: ' . $e->getMessage());
                return null;
            }            
        }
    }

    public static function getEmpresaLocal()
    {
        return DB::table('empresa_local')
            ->select('empresa')
            ->get()[0]->empresa;
    }
}
