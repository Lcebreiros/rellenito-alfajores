<?php

return [
    // App info
    'app_description'           => 'Ferramenta de gestão e controle econômico',

    // Themes
    'themes_title'              => 'Temas',
    'themes_subtitle'           => 'Escolha um tema visual para personalizar sua experiência',
    'theme_mode_label'          => 'Modo do tema',
    'theme_mode_hint'           => 'Escolha entre modo claro ou escuro',
    'custom_color_label'        => 'Selecione sua cor personalizada',
    'save_color_btn'            => 'Salvar Cor',
    'color_hint'                => 'Insira um código hexadecimal válido (exemplo: #6366f1) ou use o seletor de cor.',
    'instant_apply'             => 'As mudanças são aplicadas instantaneamente sem recarregar a página.',

    // Currency
    'currency_title'            => 'Moeda',
    'currency_subtitle'         => 'Escolha a moeda a ser usada em preços e valores',
    'currency_preview'          => 'Prévia:',
    'save_currency_btn'         => 'Salvar moeda',

    // Timezone
    'timezone_title'            => 'Fuso horário',
    'timezone_select_label'     => 'Selecione seu fuso horário',
    'timezone_select_hint'      => 'Configure seu fuso horário para ver datas e horários corretos em todo o app',
    'filter_by_country'         => 'Filtrar por país',
    'search_timezone'           => 'Buscar fuso horário',
    'timezone_placeholder'      => 'Cidade ou zona (ex. Buenos Aires, London)',
    'use_argentina_btn'         => '🇦🇷 Usar Argentina',
    'clear_btn'                 => 'Limpar',
    'no_countries'              => 'Nenhum país disponível.',
    'no_results'                => 'Sem resultados',
    'search_other_terms'        => 'Tente outros termos de busca',
    'tz_configured'             => 'Configurado:',
    'tz_not_configured'         => 'Não configurado',
    'save_config_btn'           => 'Salvar configuração',
    'tz_tip'                    => 'Se você trabalha na Argentina, selecione America/Argentina/Buenos_Aires para obter o horário local correto.',

    // Identity
    'identity_title'            => 'Identidade',
    'site_title_label'          => 'Título do site',
    'save_btn'                  => 'Salvar',

    // Stock Notifications
    'stock_notif_title'         => 'Notificações de Estoque',
    'stock_notif_subtitle'      => 'Configure alertas automáticos',
    'low_stock_alert_title'     => 'Alerta de Estoque Baixo',
    'low_stock_alert_desc'      => 'Receba uma notificação quando o estoque estiver abaixo do limite configurado',
    'low_stock_threshold_label' => 'Limite de estoque baixo',
    'units_suffix'              => 'unidades',
    'min_label'                 => 'Mínimo: 1',
    'notif_when_below'          => 'Notificaremos quando um produto tiver :n ou menos unidades em estoque.',
    'out_of_stock_alert_title'  => 'Alerta Sem Estoque',
    'out_of_stock_alert_desc'   => 'Receba uma notificação quando um produto ficar sem estoque (0 unidades)',
    'notif_all_products'        => 'As notificações se aplicam a todos os seus produtos',

    // Rentals hours
    'rentals_title'             => 'Aluguéis / Quadras',
    'rentals_subtitle'          => 'Horário operacional para reservas',
    'rentals_hours_desc'        => 'Configure o horário de abertura e fechamento para exibir os slots disponíveis na vista de quadras.',
    'open_time_label'           => 'Abertura',
    'close_time_label'          => 'Fechamento',
    'save_hours_btn'            => 'Salvar horário',

    // Google Calendar
    'google_cal_subtitle'       => 'Sincronização automática de eventos',
    'google_connected'          => 'Conta conectada',
    'auto_sync_title'           => 'Sincronização automática',
    'auto_sync_desc'            => 'As vendas agendadas serão salvas automaticamente no seu Google Calendar',
    'syncs_auto_title'          => 'Sincroniza automaticamente:',
    'sync_item_orders'          => 'Vendas agendadas (quando você cria ou edita uma venda com data)',
    'sync_item_update'          => 'Atualização automática ao alterar datas ou detalhes',
    'sync_item_delete'          => 'Exclusão automática ao cancelar ou excluir vendas',
    'sync_disabled_title'       => 'Sincronização desativada',
    'sync_disabled_desc'        => 'As novas vendas serão salvas apenas no Gestior. Ative a sincronização para ver suas vendas no Google Calendar.',
    'privacy_note'              => 'Privacidade: Acessamos seu calendário apenas para criar eventos das suas vendas. Não compartilhamos suas informações com terceiros. Você pode desconectar sua conta a qualquer momento.',
    'disconnect_confirm'        => "Tem certeza que deseja desconectar sua conta do Google Calendar?\n\nOs eventos existentes permanecerão no seu calendário, mas novas vendas não serão sincronizadas.",
    'disconnect_google_btn'     => 'Desconectar Google Calendar',
    'connect_benefits_title'    => 'Ao conectar seu Google Calendar:',
    'benefit_1'                 => 'Suas vendas agendadas aparecerão automaticamente no seu calendário',
    'benefit_2'                 => 'Você receberá lembretes automáticos antes de cada venda',
    'benefit_3'                 => 'Você verá suas vendas em todos os seus dispositivos sincronizados',
    'benefit_4'                 => 'As alterações são atualizadas em tempo real',
    'privacy_title'             => 'Sua privacidade é importante:',
    'privacy_1'                 => 'Acessamos seu calendário apenas para criar eventos de vendas',
    'privacy_2'                 => 'Não lemos seus outros eventos nem informações pessoais',
    'privacy_3'                 => 'Não compartilhamos seus dados com terceiros',
    'privacy_4'                 => 'Você pode desconectar a qualquer momento',
    'connect_google_btn'        => 'Conectar com Google Calendar',
    'google_redirect_hint'      => 'Você será redirecionado ao Google para autorizar o acesso com segurança',

    // Receipt logo
    'receipt_logo_title'        => 'Logo do Comprovante',
    'preview_label'             => 'Prévia',
    'dropzone_label'            => 'Arraste uma imagem ou clique',
    'dropzone_hint'             => 'Aceita .png, .jpg, .jpeg, .webp (máx 2 MB)',
    'remove_btn'                => 'Remover',
    'receipt_logo_tip'          => 'Dica: envie uma versão horizontal com fundo transparente para que fique bem no comprovante.',

    // Modules config
    'modules_title'             => 'Módulos do Painel',
    'modules_subtitle'          => 'Personalize quais módulos você vê na sua barra lateral',
    'modules_info'              => 'Selecione apenas os módulos que você usa no seu negócio. Os módulos fixos (Dashboard, Vendas, Métodos de Pagamento, Estoque, Gastos, Configurações e Suporte) sempre estarão disponíveis.',
    'module_visible'            => 'Visível',
    'module_hidden'             => 'Oculto',
    'modules_selected_suffix'   => 'módulos selecionados',
    'of_label'                  => 'de',
    'save_changes_btn'          => 'Salvar alterações',

    // Generic
    'saving'                    => 'Salvando...',

    // App logo form
    'logo_no_logo'      => 'Sem logo',
    'logo_current'      => 'Logo atual',
    'logo_hint'         => 'Recomendado: PNG com fundo transparente, mínimo 128×128.',
    'logo_uploading'    => 'Enviando…',
    'logo_save_btn'     => 'Salvar logo',
    'logo_remove_btn'   => 'Remover logo',

    // Profile page
    'profile_heading'          => 'Meu perfil',
    'profile_page_title'       => 'Configurações da conta',
    'profile_page_subtitle'    => 'Gerencie suas informações pessoais, segurança e preferências do app.',
    'profile_personal_title'   => 'Informações pessoais',
    'profile_personal_sub'     => 'Nome, email e foto de perfil.',
    'profile_password_title'   => 'Segurança: Senha',
    'profile_password_sub'     => 'Atualize sua senha periodicamente.',
    'profile_2fa_title'        => 'Autenticação em dois fatores (2FA)',
    'profile_2fa_sub'          => 'Proteja sua conta com uma segunda etapa de verificação.',
    'profile_logo_title'       => 'Personalização: Logo do aplicativo',
    'profile_logo_sub'         => 'Envie um logo para o cabeçalho e o menu lateral.',
    'profile_sessions_title'   => 'Sessões do navegador',
    'profile_sessions_sub'     => 'Encerre outras sessões ativas.',
    'profile_delete_title'     => 'Excluir conta',
    'profile_delete_sub'       => 'Excluirá permanentemente seus dados.',

    // Payment method selector
    'pm_select_title'   => 'Formas de Pagamento',
    'pm_select_aria'    => 'Selecionar :name',
    'pm_no_methods'     => 'Nenhuma forma de pagamento configurada',
    'pm_no_methods_hint'=> 'Acesse <a href=":url" class="underline hover:text-amber-900 dark:hover:text-amber-100 font-semibold">Formas de Pagamento</a> para adicionar.',
];
