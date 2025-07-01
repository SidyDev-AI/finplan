# 📄 Documentação – Projeto **FinPlan** (PHP + SQLite)

## 🛠️ Requisitos

- [XAMPP](https://www.apachefriends.org) instalado (inclui PHP e Apache)
- PHP 7.4 ou superior
- Navegador web (Chrome, Firefox, etc.)

---

## ⚙️ Configuração do XAMPP (Apache)

1. Abra o arquivo de configuração do Apache:
"C:\xampp\apache\conf\httpd.conf" ou a pasta que você instalou o XAMPP

2. Substitua a linha:
```apache
DocumentRoot "C:/xampp/htdocs"
<Directory "C:/xampp/htdocs">

Por : 

DocumentRoot "Pasta onde está o seu projeto"
<Directory "Pasta onde está o seu projeto">
    #
    # Possible values for the Options directive are "None", "All",
    # or any combination of:
    #   Indexes Includes FollowSymLinks SymLinksifOwnerMatch ExecCGI MultiViews
    #
    # Note that "MultiViews" must be named *explicitly* --- "Options All"
    # doesn't give it to you.
    #
    # The Options directive is both complicated and important.  Please see
    # http://httpd.apache.org/docs/2.4/mod/core.html#options
    # for more information.
    #
    Options Indexes FollowSymLinks Includes ExecCGI

    #
    # AllowOverride controls what directives may be placed in .htaccess files.
    # It can be "All", "None", or any combination of the keywords:
    #   AllowOverride FileInfo AuthConfig Limit
    #
    AllowOverride All

    #
    # Controls who can get stuff from this server.
    #
    Require all granted
</Directory>
````

3. Salve e reinicie o Apache pelo XAMPP Control Panel.

✅ Como Executar o Projeto : 

1. Inicie o Apache no XAMPP.
2. Abra o navegador e acesse: 
   http://localhost/

3. A página inicial do sistema (tela de login ou cadastro) será exibida.