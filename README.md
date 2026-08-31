# MyFamalicão 🗺️

**MyFamalicão** é uma Progressive Web App (PWA) desenvolvida no âmbito da **Prova de Aptidão Profissional (PAP)** do curso profissional de **Técnico de Gestão e Programação de Sistemas Informáticos (2025/2026)**.

O projeto tem como objetivo promover **Vila Nova de Famalicão**, permitindo aos utilizadores descobrir pontos de interesse, criar roteiros personalizados e explorar a cidade através de um mapa interativo.

> Projeto desenvolvido por **Rodrigo Afonso Loureiro de Frutuoso** — Agrupamento de Escolas Camilo Castelo Branco.

## ✨ Funcionalidades

- 🗺️ **Mapa interativo** com pontos de interesse de Vila Nova de Famalicão
- 📍 **Pontos de Interesse (POI)** com informação, imagens e localização
- 🧭 **Criação de roteiros personalizados** através da seleção dos locais a visitar
- 🚗 **Navegação com Google Maps**, permitindo abrir a rota criada diretamente no GPS
- 📏 **Cálculo de distância e duração** do percurso
- 💾 **Guardar e consultar rotas** associadas à conta do utilizador
- 📄 **Exportação de roteiros para PDF**
- 🔊 **Áudio-guia** através de síntese de voz para ouvir as descrições dos locais
- ⭐ **Criação de locais personalizados** pelo utilizador
- 👥 **Funcionalidades de comunidade**, incluindo partilha de locais
- 🏆 **Gamificação e conquistas** para incentivar a exploração da cidade
- 🌦️ **Informação meteorológica** de Famalicão
- 🌍 **Suporte para diferentes idiomas**
- 👤 **Sistema de registo, login e perfil de utilizador**
- ⚙️ **Página de definições** para personalização da conta
- 🛡️ **Área de administração** para gestão da plataforma
- 📱 **Design responsivo** para computador, tablet e smartphone
- 📲 **Progressive Web App (PWA)** com `manifest.json` e Service Worker

## 🛠️ Tecnologias utilizadas

### Frontend

- **HTML5**
- **CSS3**
- **JavaScript (ES6)**
- **Leaflet.js** — mapas interativos
- **Leaflet Routing Machine** — cálculo e representação de rotas
- **Phosphor Icons** — iconografia
- **Google Fonts — Inter**

### Backend

- **PHP**
- **MySQL**
- **PDO** para ligação à base de dados
- **PHPMailer** para envio de emails

### PWA

- Web App Manifest (`manifest.json`)
- Service Worker (`sw.js`)
- Interface adaptada a dispositivos móveis

### Serviços externos

O projeto utiliza alguns serviços e recursos externos, nomeadamente:

- **OpenStreetMap / CARTO** para os mapas
- **Leaflet** para a interface cartográfica
- **Google Maps** para navegação das rotas
- **Google Fonts** para tipografia
- **Phosphor Icons** para os ícones

## 📁 Estrutura do projeto

```text
MyFamalicaoFinal/
├── index.php                    # Página inicial
├── map.php                      # Página principal do mapa interativo
├── login.php                    # Login de utilizadores
├── register.php                 # Registo de utilizadores
├── logout.php                   # Terminar sessão
├── comunidade.php               # Área da comunidade
├── meus_locais.php              # Locais guardados/criados pelo utilizador
├── destaques.php                # Pontos e conteúdos em destaque
├── sobre.php                    # Informação sobre a PAP
├── settings.php                 # Definições do utilizador
├── admin.php                    # Área de administração
│
├── api_admin.php                # Operações da área administrativa
├── api_custom_pois.php          # Gestão de POI personalizados
├── api_gamification.php         # Sistema de gamificação
├── api_manage_poi.php           # Gestão de pontos de interesse
├── api_routes.php               # Gestão de rotas
├── api_social.php               # Funcionalidades sociais/comunidade
├── api_update_profile.php       # Atualização do perfil
├── api_upload_avatar.php        # Upload de avatar
│
├── db_connect.php               # Ligação à base de dados MySQL
├── migrate_gamification.php     # Migração relacionada com gamificação
├── migrate_language.php         # Migração relacionada com idiomas
│
├── script.js                    # Lógica principal do mapa e da aplicação
├── theme_handler.js             # Gestão do tema/interface
├── sw.js                        # Service Worker da PWA
├── manifest.json                # Configuração da PWA
│
├── style.css                    # Estilos da aplicação/mapa
├── main_style.css               # Estilos principais
├── auth_style.css               # Estilos de autenticação
├── favicon.png                  # Ícone da aplicação
│
└── includes/
    ├── mailer.php               # Configuração de envio de emails
    └── PHPMailer/               # Biblioteca PHPMailer
```

## 🚀 Instalação e configuração

### 1. Requisitos

Para executar o projeto localmente, recomenda-se utilizar:

- **PHP 8.x ou superior**
- **MySQL / MariaDB**
- **Apache**
- **XAMPP** ou outro ambiente equivalente
- Navegador moderno com suporte a JavaScript

### 2. Clonar o projeto

```bash
git clone https://github.com/r0dri12/MyFamalicaoFinal.git
cd MyFamalicaoFinal
```

### 3. Configurar o servidor

Coloca a pasta do projeto dentro do diretório `htdocs` do XAMPP:

```text
C:\xampp\htdocs\MyFamalicaoFinal
```

Inicia no XAMPP os serviços:

- Apache
- MySQL

### 4. Configurar a base de dados

A aplicação utiliza uma base de dados MySQL denominada `myfamalicao`. A ligação está definida em `db_connect.php`.

Configuração atualmente utilizada no ambiente local:

```php
$host = "localhost";
$db_name = "myfamalicao";
$username = "root";
$password = "";
```

Cria a base de dados através do **phpMyAdmin**:

```sql
CREATE DATABASE myfamalicao;
```

> **Nota:** este repositório não inclui atualmente um ficheiro SQL completo de estrutura/seed da base de dados. As tabelas necessárias devem existir antes de utilizar as funcionalidades que dependem da base de dados.

### 5. Executar a aplicação

Com Apache e MySQL ativos, abre no navegador:

```text
http://localhost/MyFamalicaoFinal/
```

## 🗺️ Como utilizar

### Explorar a cidade

1. Entra na aplicação.
2. Acede ao **Mapa** através da opção disponível na aplicação.
3. Explora os pontos de interesse apresentados no mapa.
4. Seleciona um ponto para consultar a sua informação.

### Criar uma rota

1. Seleciona os pontos que pretendes visitar.
2. Adiciona-os ao teu roteiro.
3. A aplicação calcula o percurso, incluindo distância e duração.
4. Podes guardar a rota para utilização futura.
5. Também podes abrir a rota diretamente no **Google Maps**.

### Criar um local personalizado

Os utilizadores podem selecionar uma localização no mapa e criar um novo local, indicando o seu nome e descrição. Estes locais podem posteriormente ser utilizados nos roteiros.

### Áudio-guia

Os pontos de interesse podem disponibilizar as suas descrições através de síntese de voz. Também existe a possibilidade de reproduzir o áudio-guia associado ao roteiro.

## 📱 Progressive Web App

O MyFamalicão foi desenvolvido como uma **Progressive Web App**, permitindo que a aplicação seja utilizada através do navegador e instalada como uma aplicação em dispositivos compatíveis.

A configuração da PWA encontra-se em `manifest.json` e o comportamento offline/cache é suportado pelo `sw.js`.

## ♿ Acessibilidade

A acessibilidade é uma das preocupações do projeto. Entre as funcionalidades desenvolvidas encontram-se:

- Áudio para as descrições dos pontos de interesse
- Interface adaptada a diferentes tamanhos de ecrã
- Utilização de ícones e elementos visuais para facilitar a navegação
- Possibilidade de explorar os conteúdos sem depender exclusivamente de informação visual

## 🔐 Contas e permissões

A aplicação possui diferentes níveis de utilização:

- **Visitante:** pode consultar os conteúdos públicos da aplicação.
- **Utilizador registado:** pode utilizar funcionalidades como rotas, locais personalizados, comunidade e definições de perfil.
- **Administrador:** possui acesso à área de administração e às ferramentas de gestão disponibilizadas pela aplicação.

## 🎯 Objetivos da PAP

O projeto pretende funcionar como uma plataforma digital de apoio ao turismo local, tornando mais simples e interativa a descoberta de Vila Nova de Famalicão.

Os principais objetivos são:

- Promover o património e os locais de interesse de Famalicão
- Facilitar a criação de percursos turísticos personalizados
- Incentivar a exploração da cidade
- Integrar funcionalidades de acessibilidade
- Criar uma experiência moderna e adaptada a dispositivos móveis
- Permitir a participação dos utilizadores através da comunidade

## 🔮 Possíveis melhorias futuras

Algumas funcionalidades que podem ser desenvolvidas ou melhoradas futuramente incluem:

- Sistema de recomendações baseado nas preferências do utilizador
- Mais pontos de interesse e conteúdos multimédia
- Melhor suporte offline
- Melhorias no sistema de acessibilidade
- Integração de mais serviços turísticos locais
- Sistema de avaliações e comentários mais completo
- Otimização da infraestrutura e segurança do backend

## 👨‍💻 Autor

**Rodrigo Afonso Loureiro de Frutuoso**  
Curso Profissional de Técnico de Gestão e Programação de Sistemas Informáticos  
Agrupamento de Escolas Camilo Castelo Branco  
PAP — 2025/2026

## 📄 Licença

Este projeto foi desenvolvido no âmbito de uma **Prova de Aptidão Profissional (PAP)**. Não foi definida uma licença open-source específica para este repositório.

---

⭐ Se este projeto te for útil ou interessante, considera deixar uma estrela no repositório.