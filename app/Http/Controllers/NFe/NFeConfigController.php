<?php

/**
 * Manipulacao das configuracoes da NFe
 *
 * @category NFe
 * @package  DFe
 * @author   "Bruno C. Silva" <bwmsites@gmail.com>
 * @link     fb.com/bwmsites
 */

namespace App\Http\Controllers\NFe;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\NFe\NFeConfig;
use App\Models\Pessoa;
use App\Services\UtilService as Utils;
use App\Services\CertificadoService as CertS;

class NFeConfigController extends Controller
{
    /**
     * Exibe a configuracao existente
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $conf = NFeConfig::all();
        return isset($conf[0]) ? $conf[0] : [];
    }

    /**
     * Insere uma nova configuracao
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $empresa  = $request->input('empresa');
        $tpAmb    = $request->input('tipo_ambiente');
        $tpCert   = $request->input('tipo_certificado');                
        $tpImp    = $request->input('danfe_tipo_impressao');
        $certPass = $request->input('certificado_senha');
                    
        $conf = new NFeConfig();
        $emissor = Pessoa::find($empresa);
        
        if ($emissor) {
            $api_config = $this->_setConfigAPI($tpAmb, $emissor);
            $cnpj = Utils::desformataCNPJ($emissor->cpf_cnpj);
            $nID = Utils::getIDValue();

            $certInfo = CertS::storeCertFile($cnpj, $request);
            $logo = Utils::storeLogoFile($request);

            if ($certInfo['retorno'] !== 'ok') {
                return $certInfo;
            }

            $certName = isset($certInfo['certificado']) ? $certInfo['certificado'] : null;
            $certVal  = isset($certInfo['validade']) ? $certInfo['validade'] : null;

            try {
                $conf->fill(
                    [
                    "id" => $nID,
                    "empresa" => $empresa,
                    "tipo_ambiente" => $tpAmb,
                    "tipo_certificado" => $tpCert,
                    "certificado" => $certName,
                    "certificado_senha" => $certPass,
                    "certificado_validade" => $certVal,
                    "danfe_tipo_impressao" => $tpImp,
                    "cpf_cnpj" => $cnpj,
                    "api_config" => $api_config,
                    "logo" => $logo
                    ]
                );
                $conf->save();
                $content = ["retorno" => "ok", "msg" => "Nova configuração registrada com sucesso!"];
                $ret = 200;
            } catch (\Exception $e) {
                $content = ["retorno" => "erro", "msg" => "Erro ao registrar configurações. Detalhe: " . $e->getMessage()];
                $ret = 500;
            }
        } else {
            $content = ["retorno" => "falha", "msg" => "Empresa não localizada. Configuração não pode ser concluída."];
            $ret = 500;
        }
        return \response($content, $ret);
    }

    /**
     * Exibe a configuracao existente
     *
     * @param  $config => ID vindo da tabela de configurações de certificado digital
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update($config, Request $request)
    {
        $conf = NFeConfig::find($config);
        $empresa  = $request->input('empresa');
        $tpAmb    = $request->input('tipo_ambiente');
        $tpCert   = $request->input('tipo_certificado');                
        $tpImp    = $request->input('danfe_tipo_impressao');
        $certPass = $request->input('certificado_senha');
        
        if ($conf) {
            $emissor = Pessoa::find($conf->empresa);
            $api_config = $this->_setConfigAPI($tpAmb, $emissor);
            $cnpj = Utils::desformataCNPJ($emissor->cpf_cnpj);

            $certInfo = CertS::storeCertFile($cnpj, $request);
            $logo = Utils::storeLogoFile($request);
            return $certInfo;
        } else {
            $content = ["retorno" => "falha", "msg" => "Dados não localizados"];
            $ret = 204;
        }

        return \response($content, $ret);
    }

    private function _setConfigAPI($tpAmb, $emissor)
    {
        $config = [
            "tpAmb" => intval($tpAmb),
            "razaosocial" => $emissor->razao_social,
            "cnpj" => Utils::desformataCNPJ($emissor->cpf_cnpj),
            "siglaUF" => $emissor->uf,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "",
            "CSC" => "",
            "CSCid" => ""
        ];

        return json_encode($config);
    }
}
