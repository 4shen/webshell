<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    'dashboard'          => 'Dashboard',
    'writeable_settings' => 'O diretório de configurações do Cachet não é gravável. Certifique-se de que <code>./bootstrap/cachet</code> é gravável pelo servidor web.',

    // Incidents
    'incidents' => [
        'title'                    => 'Incidentes & Agendamentos',
        'incidents'                => 'Incidentes',
        'logged'                   => '{0}Não existem incidentes, bom trabalho.|[1]Você adicionou um incidente.|[2,*]Você reportou <strong>:count</strong> incidentes.',
        'incident-create-template' => 'Criar template',
        'incident-templates'       => 'Template de incidentes',
        'updates'                  => [
            'title'   => 'Atualizações para o incidente :incident',
            'count'   => '{0}Nenhuma atualização|[1]Uma atualização|[2]Duas atualizações|[3,*]Várias atualizações',
            'add'     => [
                'title'   => 'Crie uma nova atualização de incidente',
                'success' => 'Sua atualização de incidente foi criada.',
                'failure' => 'Algo deu errado com a atualização do incidente.',
            ],
            'edit' => [
                'title'   => 'Editar atualização do incidente',
                'success' => 'Sua atualização de incidente foi atualizada.',
                'failure' => 'Algo deu errado ao atualizar as informações do incidente',
            ],
        ],
        'reported_by'              => 'Reportado por :user',
        'add'                      => [
            'title'   => 'Relatar um incidente',
            'success' => 'Incidente adicionado.',
            'failure' => 'Houve um erro ao adicionar o incidente, por favor tente novamente.',
        ],
        'edit' => [
            'title'   => 'Editar um incidente',
            'success' => 'Incidente atualizado.',
            'failure' => 'Houve um erro na edição do incidente, por favor tente novamente.',
        ],
        'delete' => [
            'success' => 'O incidente foi excluído e não aparecerá na sua página de status.',
            'failure' => 'O incidente não pode ser excluído, por favor tente novamente.',
        ],

        // Incident templates
        'templates' => [
            'title' => 'Template de incidentes',
            'add'   => [
                'title'   => 'Criar um modelo de incidente',
                'message' => 'Crie seu primeiro template de incidente.',
                'success' => 'Seu novo modelo de incidente foi criado.',
                'failure' => 'Algo deu errado com o modelo de incidente.',
            ],
            'edit' => [
                'title'   => 'Editar modelo',
                'success' => 'O modelo de incidente foi atualizado.',
                'failure' => 'Algo deu errado ao atualizar o modelo de incidente',
            ],
            'delete' => [
                'success' => 'O modelo de incidente foi excluído.',
                'failure' => 'O modelo de incidente não pode ser excluído, por favor tente novamente.',
            ],
        ],
    ],

    // Incident Maintenance
    'schedule' => [
        'schedule'     => 'Manutenção',
        'logged'       => '{0}Ainda não ocorreu nenhuma manuteção, bom trabalho. |[1]Você agendou uma manuteção. | [2, *] Você adicionou <strong>:</strong> manutenções.',
        'scheduled_at' => 'Agendada em :timestamp',
        'add'          => [
            'title'   => 'Adicionar manutenção agendada',
            'success' => 'Manutenção adicionada.',
            'failure' => 'Algo deu errado ao adicionar a Manutenção, por favor tente novamente.',
        ],
        'edit' => [
            'title'   => 'Editar Manutenção',
            'success' => 'Manutenção atualizada!',
            'failure' => 'Algo deu errado ao editar a Manutenção, por favor tente novamente.',
        ],
        'delete' => [
            'success' => 'A manutenção programada foi excluída e não aparecerá na sua página de status.',
            'failure' => 'A Manutenção não pôde ser excluída, por favor tente novamente.',
        ],
    ],

    // Components
    'components' => [
        'components'         => 'Componentes',
        'component_statuses' => 'Status do componente',
        'listed_group'       => 'Agrupados sob :name',
        'add'                => [
            'title'   => 'Adicionar componente',
            'message' => 'Você deve adicionar um componente.',
            'success' => 'Componente criado.',
            'failure' => 'Algo deu errado com o componente, por favor tente novamente.',
        ],
        'edit' => [
            'title'   => 'Editar um componente',
            'success' => 'Componente atualizado.',
            'failure' => 'Algo deu errado com o componente, por favor tente novamente.',
        ],
        'delete' => [
            'success' => 'O componente foi excluído!',
            'failure' => 'O componente não pode ser excluído. Por favor tente de novo.',
        ],

        // Component groups
        'groups' => [
            'groups'        => 'Grupo de componente|Grupos de componente',
            'no_components' => 'Você deve adicionar um componente à um grupo.',
            'add'           => [
                'title'   => 'Adicionar um grupo de componentes',
                'success' => 'Grupo de componentes adicionado.',
                'failure' => 'Algo deu errado com o componente, por favor tente novamente.',
            ],
            'edit' => [
                'title'   => 'Editar um grupo de componentes',
                'success' => 'Grupo de componentes atualizado.',
                'failure' => 'Algo deu errado com o componente, por favor tente novamente.',
            ],
            'delete' => [
                'success' => 'O grupo de componentes foi excluído!',
                'failure' => 'O grupo de componentes não pode ser excluído, por favor tente novamente.',
            ],
        ],
    ],

    // Metrics
    'metrics' => [
        'metrics' => 'Metricas',
        'add'     => [
            'title'   => 'Criar uma métrica',
            'message' => 'Você deveria adicionar uma métrica.',
            'success' => 'Métrica criada.',
            'failure' => 'Algo deu errado com a métrica, por favor tente novamente.',
        ],
        'edit' => [
            'title'   => 'Editar uma métrica',
            'success' => 'Métrica atualizada.',
            'failure' => 'Algo deu errado com a métrica, por favor tente novamente.',
        ],
        'delete' => [
            'success' => 'A métrica foi excluída e não será mais exibida na sua página de status.',
            'failure' => 'A métrica não pode ser excluída, por favor tente novamente.',
        ],
    ],
    // Subscribers
    'subscribers' => [
        'subscribers'          => 'Assinantes',
        'description'          => 'Assinantes vão receber atualizações de e-mail quando incidentes criados ou componentes atualizados.',
        'description_disabled' => 'Para utilizar esse recurso, você precisa permitir que as pessoas se cadastrem para notificações.',
        'verified'             => 'Verificado',
        'not_verified'         => 'Não verificado',
        'subscriber'           => ':email, inscreveu-se em :date',
        'no_subscriptions'     => 'Inscrito em todas as atualizações',
        'global'               => 'Inscrito globalmente',
        'add'                  => [
            'title'   => 'Adicionar um novo assinante',
            'success' => 'Inscrito adicionado.',
            'failure' => 'Algo deu errado adicionando o assinante, por favor tente novamente.',
            'help'    => 'Insira cada assinante em uma nova linha.',
        ],
        'edit' => [
            'title'   => 'Atualizar assinante',
            'success' => 'Inscrito atualizado.',
            'failure' => 'Algo deu errado editando o assinante, por favor tente novamente.',
        ],
    ],

    // Team
    'team' => [
        'team'        => 'Equipe',
        'member'      => 'Membro',
        'profile'     => 'Perfil	',
        'description' => 'Membros da equipe serão capazes de adicionar, modificar &amp; editar componentes e incidentes.',
        'add'         => [
            'title'   => 'Adicionar um novo membro da equipe',
            'success' => 'Membro da equipe adicionado.',
            'failure' => 'Membro da equipe não pôde ser adicionado, por favor tente novamente.',
        ],
        'edit' => [
            'title'   => 'Atualizar perfil',
            'success' => 'Perfil atualizado.',
            'failure' => 'Algo deu errado atualizando o perfil, por favor tente novamente.',
        ],
        'delete' => [
            'success' => 'O membro da equipe foi excluído e não conseguirá mais acessar o painel de controle!',
            'failure' => 'Membro da equipe não pôde ser adicionado, por favor tente novamente.',
        ],
        'invite' => [
            'title'   => 'Convidar um novo membro para a equipe',
            'success' => 'Convite enviado',
            'failure' => 'O convite não pôde ser enviado, por favor tente novamente.',
        ],
    ],

    // Settings
    'settings' => [
        'settings'  => 'Configurações',
        'app-setup' => [
            'app-setup'   => 'Instalação do aplicativo',
            'images-only' => 'Somente imagens podem ser carregadas.',
            'too-big'     => 'O arquivo que você carregou é muito grande. Envie uma imagem inferior à :size',
        ],
        'analytics' => [
            'analytics' => 'Estatísticas',
        ],
        'log' => [
            'log' => 'Log',
        ],
        'localization' => [
            'localization' => 'Idioma',
        ],
        'customization' => [
            'customization' => 'Personalização',
            'header'        => 'HTML de cabeçalho personalizado',
            'footer'        => 'HTML de rodapé personalizado',
        ],
        'mail' => [
            'mail'  => 'E-Mail',
            'test'  => 'Teste',
            'email' => [
                'subject' => 'Notificação de teste do Cachet',
                'body'    => 'Esta é uma notificação de teste do Cachet.',
            ],
        ],
        'security' => [
            'security'   => 'Segurança',
            'two-factor' => 'Usuários sem autenticação de dois fatores',
        ],
        'stylesheet' => [
            'stylesheet' => 'Folha de estilo',
        ],
        'theme' => [
            'theme' => 'Tema',
        ],
        'edit' => [
            'success' => 'Configurações salvas.',
            'failure' => 'Não foi possível salvar as configurações.',
        ],
        'credits' => [
            'credits'       => 'Créditos',
            'contributors'  => 'Colaboradores',
            'license'       => 'Cachet é um projeto de código aberto com licença BSD-3, lançado pela <a href="https://alt-three.com/?utm_source=cachet&utm_medium=credits&utm_campaign=Cachet%20Credit%20Dashboard" target="_blank"> Alt Three Services Limited</a>.',
            'backers-title' => 'Apoiadores e Patrocinadores',
            'backers'       => 'Se você deseja apoiar o desenvolvimento, confira a campanha do <a href="https://patreon.com/jbrooksuk" target="_blank">Cachet no Pantreon</a>.',
            'thank-you'     => 'Agradeço a cada um dos :count colaboradores.',
        ],
    ],

    // Login
    'login' => [
        'login'      => 'Login',
        'logged_in'  => 'Você está logado.',
        'welcome'    => 'Bem-vindo de volta!',
        'two-factor' => 'Por favor insira o seu token.',
    ],

    // Sidebar footer
    'help'        => 'Ajuda',
    'status_page' => 'Página de status',
    'logout'      => 'Sair',

    // Notifications
    'notifications' => [
        'notifications' => 'Notificações',
        'awesome'       => 'Excelente.',
        'whoops'        => 'Opa.',
    ],

    // Widgets
    'widgets' => [
        'support'          => 'Ajude o Cachet',
        'support_subtitle' => 'Confira nossa campanha no <strong><a href="https://patreon.com/jbrooksuk" target="_blank">Patreon</a></strong>!',
        'news'             => 'Últimas Notícias',
        'news_subtitle'    => 'Receba as últimas atualizações',
    ],

    // Welcome modal
    'welcome' => [
        'welcome' => 'Bem-vindo à sua nova página de status!',
        'message' => 'Sua página de status está quase pronta! Tavez vocë queira checar essas configurações extras',
        'close'   => 'Me leve direto para o meu painel de controle',
        'steps'   => [
            'component'  => 'Criar componentes',
            'incident'   => 'Criar incidentes',
            'customize'  => 'Personalizar',
            'team'       => 'Adicionar usuários',
            'api'        => 'Gerar token de API',
            'two-factor' => 'Autenticação em duas etapas',
        ],
    ],

];
