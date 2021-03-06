Delibera
========

O Delibera é um plugin para WordPress para democracia digital. Ele permite a discussão e deliberação de encaminhamentos relacionados a uma pauta por uma comunidade de usuários.

Inspirado em conceitos de redes sociais e outras mídias digitais, tão amplamente apropriadas pela sociedade atual, o Delibera é feito para que pessoas possam interagir, trocar conteúdo e tomar decisões acerca dos assuntos de interesse para diversos tipos de organização ou evento, potencializando o ambiente virtual para a transformação do mundo real.

![Screenshot](./screenshots/como.png)

[![Stories in Ready](https://badge.waffle.io/redelivre/delibera.png?label=ready&title=Ready)](https://waffle.io/redelivre/delibera)
[![Build
Status](http://jenkins.beta.redelivre.org.br:8081/buildStatus/icon?job=Beta%20Delibera)](https://github.com/redelivre/delibera)
[![Join the chat at https://telegram.me/IdentidadeDigital](https://patrolavia.github.io/telegram-badge/chat.png)](https://t.me/RedeLivreOrg)

# capturas
![Screenshot](./screenshots/0.png)
![Screenshot](./screenshots/1.png)

Ele funciona em quatro momentos:
 - Criação e validação de pautas
 -  Discussão das pautas e apresentação das propostas de encaminhamento
 -  Sistematização dos encaminhamentos
 -  Votação dos encaminhamentos

O Delibera é ideal para quaisquer grupos de pessoas que precisem tomar decisões coletivas. Conselhos deliberativos, assembleias de condomínio, associações de bairro, movimentos sociais, coletivos, partidos políticos, mandatos, organizações colaborativas e governos.

Como usar?
==========

Tudo o que você precisa para rodar este plugin é ter uma instalação de WordPress funcionando e saber como instalar um plugin. Para mais informações veja a [documentação](https://github.com/redelivre/delibera/wiki/Home).

Acessando pautas:

Post type = pauta

Exemplo sem links customizados:
http://www.exemplo.com/?post_type=pauta

Exemplo com links customizados:
http://www.exemplo.com/pauta/

Como Colaborar?
===============

Existem algumas maneiras de colaborar com o desenvolvimento deste plugin. Se você é um usuário, você pode ter encontrado um problema (bug) ou ter tido alguma idéia de uma nova funcionalidade. Em qualquer um dos casos, entre em contato com os desenvolvedores criando uma [issue](https://github.com/redelivre/delibera/issues).

Se você é um desenvolvedor, sinta-se livre para fazer um fork e contribuir com o desenvolvimento. Se quiser, dê uma olhada se você pode resolver alguma das [issues abertas](https://github.com/redelivre/delibera/issues).


### Situações possíveis da pauta

| Slugs           | Module          | Descrição  |
| --------------- |:---------------:| -----:|
| validacao       | Mod. validation | Proposta de pauta, precisa ser validada para ser continuar no fluxo, comentário é apenas se valida |
| naovalidada     | Mod. validation | Proposta recusada por prazo sem minimo de validação ou forçada pelo admin |
| discussao       | Mod. Discussion | Pauta em discussão, aceita comentários do tipo padrão ou encaminhamento    |
| eleicao_relator | Mod. rapporteur | Pauta que precisa de relator, mas antes o relator precisa ser eleito (não implementado)   |
| relatoria       | Mod. rapporteur | o Relator vai editar os encaminhamentos para criar opções válidas para votação   |
| emvotacao       | Mod. vote       |  Pauta em votação, aqui o comentário é um voto simples  |
| comresolucao    | Mod. result     |  Pauta chegou ao fim, apresentar resultado  |

## Licença

[GPL-3.0+](LICENSE.txt)
