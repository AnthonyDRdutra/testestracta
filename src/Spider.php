<?php

namespace src;

class Spider
{
    private string $url = 'http://www.sintegra.fazenda.pr.gov.br/sintegra/';
    private string $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 Safari/537.36';

    /**
     * @return array
     */
    private function defaultHeaders(): array
    {
        return [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 1,
            CURLOPT_COOKIEFILE => './cookies/cookies.txt',
            CURLOPT_COOKIEJAR => './cookies/cookies.txt',
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded', 'Cache-Control: max-age=0', 'Connection: keep-alive'],
            CURLOPT_USERAGENT => $this->agent,
            CURLOPT_FOLLOWLOCATION => 1
        ];
    }

    private array $regex = [
        'FIND_BUTTON' => '/id="consultar"/',
        'FIND_NEXT' => '/value="(.*?)"/',
        'FIND_LABELS' => '/<td class="form_label".*?>(.*?)<\/td>/si',
        'FIND_VALUES' => '/<td class="form_conteudo".*?>(.*?)<\/td>/si',
        'FIND_ERROR_MESSAGE' => '/<td class="erro_label" align="right">Motivo:<\/td>\s*<td class="erro_msg_custom">(.*?)<\/td>/'
    ];

    /**
     * @param resource $curl
     * @return void
     */
    private function Captcha(&$curl): void
    {
        $curl = curl_init();

        try {
            $setup = $this->defaultHeaders();
            $setup[CURLOPT_URL] = $this->url . 'captcha?' . (float)rand() / (float)getrandmax();
            $setup[CURLOPT_POST] = 1;

            curl_setopt_array($curl, $setup);

            $response = curl_exec($curl);
            $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $body = substr($response, $header_size);

            $save_as = 'captcha.jpeg';
            file_put_contents($save_as, $body);

            $captcha = (string)readline('Insira o captcha: ');

            $this->sendForm($captcha, $curl);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    /**
     * @return void
     */
    public function DatabyCnpj(): void
    {
        $curl = curl_init($this->url);

        try {
            $setup = $this->defaultHeaders();

            curl_setopt_array($curl, $setup);

            $response = curl_exec($curl);
            if ($response === false) {
                throw new Exception("Curl error: " . curl_error($curl));
            }

            $this->Captcha($curl);

            curl_close($curl);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    /**
     * @param string $response
     * @return array
     */
    private function LabelsAndValues($response): array
    {
        $decodedData = html_entity_decode($response);

        $start = stripos($decodedData, "<form");
        $end = stripos($decodedData, "<p class=\"obs\">", $start);
        $length = $end - $start;

        $htmlSelection = substr($decodedData, $start, $length);

        preg_match_all($this->regex['FIND_LABELS'], $htmlSelection, $labels);
        preg_match_all($this->regex['FIND_VALUES'], $htmlSelection, $values);
        $keys = str_replace(":", "", $labels[1]);

        $results = [];

        foreach ($values[1] as $k => $value) {
            if ($keys[$k] === 'Atividade(s) Econômica(s) Secundária(s)') {
                $Array = explode('<br />', $value);
                $formattedArray = [];
                foreach ($Array as $activity) {
                    $formattedArray[] = trim($activity);
                }
                $results[$keys[$k]] = $formattedArray;
            } else {
                $results[$keys[$k]] = trim($value);
            }
        }

        array_pop($results);
        return $results;
    }

    /**
     * @param string $captcha
     * @param resource $curl
     * @return void
     */
    private function sendForm($captcha, $curl): void
    {
        $cnpj = (string)readline('Insira o CNPJ: ');

        $inputs = [
            '_method' => 'POST',
            'data[Sintegra1][CodImage]' => $captcha,
            'data[Sintegra1][Cnpj]' => $cnpj,
            'empresa' => 'Consultar Empresa',
            'data[Sintegra1][Cadicms]' => '',
            'data[Sintegra1][CadicmsProdutor]' => '',
            'data[Sintegra1][CnpjCpfProdutor]' => ''
        ];

        $setup = $this->defaultHeaders();
        $setup[CURLOPT_URL] = $this->url;
        $setup[CURLOPT_POSTFIELDS] = http_build_query($inputs);
        $setup[CURLOPT_POST] = 1;

        try {
            curl_setopt_array($curl, $setup);
            $response = curl_exec($curl);
            curl_close($curl);

            if (preg_match($this->regex['FIND_ERROR_MESSAGE'], $response, $matches)) {
                $errorMessage = $matches[1];
                $errorMessage = mb_convert_encoding($errorMessage, 'UTF-8', 'ISO-8859-1');
                echo "Error: " . $errorMessage;
                return;
            } else {
                $companyInfo = $this->getInfo($curl, $response);
                print_r($companyInfo);
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    /**
     * @param resource $curl
     * @param string $response
     * @return array
     */
    private function getInfo($curl, $response): array
    {
        $Info = [];

        while (true) {
            $Info[] = $this->LabelsAndValues($response);
            preg_match($this->regex['FIND_BUTTON'], $response, $button);
            if (empty($button)) {
                break;
            }
            preg_match_all($this->regex['FIND_NEXT'], $response, $match);
            $request = $match[1][1];
            $response = $this->getNextPage($curl, $request);
        }

        return $Info;
    }

    /**
     * @param resource $curl
     * @param string $request
     * @return bool|string
     */
    private function getNextPage($curl, $request): bool|string
    {
        $inputs = [
            '_method' => 'POST',
            'data[Sintegra1][campoAnterior]: ' => $request,
            'consultar' => '',
        ];

        try {
            $data = http_build_query($inputs);

            $setup = $this->defaultHeaders();
            $setup[CURLOPT_URL] = $this->url . 'sintegra1/consultar';
            $setup[CURLOPT_POSTFIELDS] = $data;
            $setup[CURLOPT_POST] = 1;

            curl_setopt_array($curl, $setup);
            $response = curl_exec($curl);
            curl_close($curl);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }

        return $response;
    }
}
