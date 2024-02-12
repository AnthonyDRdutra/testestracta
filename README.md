# teste-eStracta
Spider em PHP que CNPJ e captcha como input e retorna um array com os dados do CNPJ solicitado a partir dos dados disponíveis publicamente em http://www.sintegra.fazenda.pr.gov.br/.
## Tecnologias
CURL PHP

### Como usar
O spider é capaz de retornar apenas CNPJs do estado do Paraná. 
Para o utilizar basta que clone o repostório (```https://github.com/AnthonyDRdutra/teste-eStracta.git``` ou ```gh repo clone AnthonyDRdutra/teste-eStracta```) e inicie index.php pela sua IDE carregando o projeto, ou pelo CMD pelos comandos:
```
php index.php
```

### Funcionalidades
-Download da Captcha direto da página.

-Validação de input, prevenindo erro de input do usuário, além de retornar informação detalhada do erro direto da página:

![error solver](https://github.com/AnthonyDRdutra/teste-eStracta/assets/97138694/fe3ffb33-9bd5-4b06-a261-989168dc1f4f)



-Retorno de dados em array chaveado:

![arraychaveado](https://github.com/AnthonyDRdutra/teste-eStracta/assets/97138694/b2abea60-4316-40b5-b4a2-7c7df9d20f04)



### Arquivos
O arquivo index.php contém o código que realiza a chamada de nossa classe Spider.php, que está dentro da pasta src/, quanto iniciado o código ele realizará o download do arquivo .jpeg do captcha, que o usuário utilizará para resolver o captcha solicitado. 

Haverá também um arquivo .txt chamado "output_results", o qual mostra exemplos do output gerado pelo código. 
