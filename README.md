# teste-eStracta
Spider em PHP que CNPJ e captcha como input e retorna um array com os dados do CNPJ solicitado a partir dos dados disponíveis publicamente em http://www.sintegra.fazenda.pr.gov.br/.
## Tecnologias
CURL PHP

### Como usar
O spider é capaz de retornar apenas CNPJs do estado do Paraná. 
Para o utilizar basta que clone o repostório e inicie index.php pela sua IDE carregando o projeto, ou pelo CMD pelos comandos:
```
php index.php
```

### Funcionalidades
-Download da Captcha direto da página.

-Validação de input, prevenindo erro de input do usuário, além de retornar informação detalhada do erro direto da página:

![error solver](https://github.com/AnthonyDRdutra/teste-eStracta/assets/97138694/0805ebf0-0c4f-4ca6-828a-77f0a6e399a3)



-Retorno de dados em array chaveado:

![arraychaveado](https://github.com/AnthonyDRdutra/teste-eStracta/assets/97138694/dca5f277-01a4-4398-9297-e795e46899c7)


### Arquivos
O arquivo index.php contém o código que realiza a chamada de nossa classe Spider.php, que está dentro da pasta src/, quanto iniciado o código ele realizará o download do arquivo .jpeg do captcha, que o usuário utilizará para resolver o captcha solicitado. Haverá também um arquivo .txt chamado "output_results", o qual mostra exemplos do output gerado pelo código. 
