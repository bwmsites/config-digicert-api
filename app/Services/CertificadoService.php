<?php

namespace App\Services;

use NFePHP\Common\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CertificadoService {
    
    private static function validaCertificado($certFile, $certPass, $empCNPJ) {
        try {        
            $certificado = Certificate::readPfx($certFile, $certPass);
            $valid    = $certificado->getValidTo();        
            $validade = $valid->format('Y-m-d');
            $empresa  = $certificado->getCompanyName();
            $cnpj     = $certificado->getCnpj();        

            $retorno  = 'ok';
            $mensagem = 'Certificado validado com sucesso.';

            /*if ($empCNPJ != $cnpj) {
                $retorno  = 'falha';
                $mensagem = 'Certificado não pertence ao cnpj informado';
            }*/

            if ($certificado->isExpired()) {
                $retorno  = 'falha';
                $mensagem = 'Certificado expirado. Validade: ' + $valid->format('d/m/Y');
            }

            return [
                "retorno" => $retorno,
                "mensagem" => $mensagem,
                "empresa" => $empresa,
                "cnpj" => $cnpj,
                "validade" => $validade
            ];
        } catch (\Exception $e) {
            return ["retorno" => "falha", "mensagem" => "Arquivo de certificado inválido ou senha incorreta"];
        }
    }

    public static function storeCertFile($cnpjEmp, Request $req) {
        if ($req->hasFile('certfile') && $req->file('certfile')->isValid()) {
            $cert = $req->file('certfile');

            $exts = ['p12', 'pfx'];
            $ext  = $cert->getClientOriginalExtension();
            if (!\in_array($ext, $exts)) {
                $retorno  = 'erro';
                $mensagem = 'Extensão de arquivo inválida. São permitidos apenas arquivos do tipo .pfx e .p12';

                return ["retorno"=>$retorno, "mensagem"=>$mensagem, "extensao"=>$ext];
            }
            
            $realPath = $cert->getRealPath();
            $certPass = $req->input('certificado_senha');           

            $valida = self::validaCertificado(file_get_contents($realPath), $certPass, $cnpjEmp);

            $certName = $cert->getClientOriginalName();
            $certData = Arr::add($valida, "certificado", $certName);

            $finaldir = \config_path('nfe' . DIRECTORY_SEPARATOR . 'certificado');
            $upload = $cert->move($finaldir, $certName);

            return $certData;
        }

        return ["retorno"=>"ok", "mensagem"=>"Sem certificado para processar."];
    }
}