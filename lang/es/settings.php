<?php

return [
    // App info
    'app_description'           => 'Herramienta de gestión y control económico',

    // Themes
    'themes_title'              => 'Temas',
    'themes_subtitle'           => 'Elegí un tema visual para personalizar tu experiencia',
    'theme_mode_label'          => 'Modo del tema',
    'theme_mode_hint'           => 'Elegí entre modo claro u oscuro',
    'custom_color_label'        => 'Seleccioná tu color personalizado',
    'save_color_btn'            => 'Guardar Color',
    'color_hint'                => 'Ingresá un código hexadecimal válido (ejemplo: #6366f1) o usá el selector de color.',
    'instant_apply'             => 'Los cambios se aplican instantáneamente sin recargar la página.',

    // Currency
    'currency_title'            => 'Moneda',
    'currency_subtitle'         => 'Elegí la moneda que se usará en precios y montos',
    'currency_preview'          => 'Vista previa:',
    'save_currency_btn'         => 'Guardar moneda',

    // Timezone
    'timezone_title'            => 'Zona horaria',
    'timezone_select_label'     => 'Seleccioná tu zona horaria',
    'timezone_select_hint'      => 'Configurá tu zona horaria para ver fechas y horarios correctos en toda la aplicación',
    'filter_by_country'         => 'Filtrar por país',
    'search_timezone'           => 'Buscar zona horaria',
    'timezone_placeholder'      => 'Ciudad o zona (ej. Buenos Aires, London)',
    'use_argentina_btn'         => '🇦🇷 Usar Argentina',
    'clear_btn'                 => 'Limpiar',
    'no_countries'              => 'No hay países disponibles.',
    'no_results'                => 'Sin resultados',
    'search_other_terms'        => 'Probá con otros términos de búsqueda',
    'tz_configured'             => 'Configurada:',
    'tz_not_configured'         => 'Sin configurar',
    'save_config_btn'           => 'Guardar configuración',
    'tz_tip'                    => 'Si trabajás en Argentina, seleccioná America/Argentina/Buenos_Aires para obtener la hora local correcta.',

    // Identity
    'identity_title'            => 'Identidad',
    'site_title_label'          => 'Título del sitio',
    'save_btn'                  => 'Guardar',

    // Stock Notifications
    'stock_notif_title'         => 'Notificaciones de Stock',
    'stock_notif_subtitle'      => 'Configurá alertas automáticas',
    'low_stock_alert_title'     => 'Alerta de Stock Bajo',
    'low_stock_alert_desc'      => 'Recibí una notificación cuando el stock esté por debajo del umbral configurado',
    'low_stock_threshold_label' => 'Umbral de stock bajo',
    'units_suffix'              => 'unidades',
    'min_label'                 => 'Mínimo: 1',
    'notif_when_below'          => 'Te notificaremos cuando un producto tenga :n o menos unidades en stock.',
    'out_of_stock_alert_title'  => 'Alerta Sin Stock',
    'out_of_stock_alert_desc'   => 'Recibí una notificación cuando un producto se quede sin stock (0 unidades)',
    'notif_all_products'        => 'Las notificaciones se aplican a todos tus productos',

    // Rentals hours
    'rentals_title'             => 'Alquileres / Canchas',
    'rentals_subtitle'          => 'Horario operativo para reservas',
    'rentals_hours_desc'        => 'Configurá el horario de apertura y cierre que se usará para mostrar los slots disponibles en la vista de canchas.',
    'open_time_label'           => 'Apertura',
    'close_time_label'          => 'Cierre',
    'save_hours_btn'            => 'Guardar horario',

    // Google Calendar
    'google_cal_subtitle'       => 'Sincronización automática de eventos',
    'google_connected'          => 'Cuenta conectada',
    'auto_sync_title'           => 'Sincronización automática',
    'auto_sync_desc'            => 'Los ventas agendadas se guardarán automáticamente en tu Google Calendar',
    'syncs_auto_title'          => 'Se sincroniza automáticamente:',
    'sync_item_orders'          => 'Ventas agendadas (cuando creás o modificás una venta con fecha)',
    'sync_item_update'          => 'Actualización automática al cambiar fechas o detalles',
    'sync_item_delete'          => 'Eliminación automática al cancelar o eliminar ventas',
    'sync_disabled_title'       => 'Sincronización desactivada',
    'sync_disabled_desc'        => 'Los ventas nuevas solo se guardarán en Gestior. Activá la sincronización para ver tus ventas en Google Calendar.',
    'privacy_note'              => 'Privacidad: Solo accedemos a tu calendario para crear eventos de tus ventas. No compartimos tu información con terceros. Podés desconectar tu cuenta en cualquier momento.',
    'disconnect_confirm'        => "¿Estás seguro de que querés desconectar tu cuenta de Google Calendar?\n\nLos eventos existentes permanecerán en tu calendario, pero no se sincronizarán nuevas ventas.",
    'disconnect_google_btn'     => 'Desconectar Google Calendar',
    'connect_benefits_title'    => 'Al conectar tu Google Calendar:',
    'benefit_1'                 => 'Tus ventas agendadas aparecerán automáticamente en tu calendario',
    'benefit_2'                 => 'Recibirás recordatorios automáticos antes de cada venta',
    'benefit_3'                 => 'Verás tus ventas en todos tus dispositivos sincronizados',
    'benefit_4'                 => 'Los cambios se actualizan en tiempo real',
    'privacy_title'             => 'Tu privacidad es importante:',
    'privacy_1'                 => 'Solo accedemos a tu calendario para crear eventos de ventas',
    'privacy_2'                 => 'No leemos tus otros eventos ni información personal',
    'privacy_3'                 => 'No compartimos tus datos con terceros',
    'privacy_4'                 => 'Podés desconectar en cualquier momento',
    'connect_google_btn'        => 'Conectar con Google Calendar',
    'google_redirect_hint'      => 'Serás redirigido a Google para autorizar el acceso de forma segura',

    // Receipt logo
    'receipt_logo_title'        => 'Logo del Comprobante',
    'preview_label'             => 'Vista previa',
    'dropzone_label'            => 'Arrastrá una imagen o hacé clic',
    'dropzone_hint'             => 'Acepta .png, .jpg, .jpeg, .webp (máx 2 MB)',
    'remove_btn'                => 'Eliminar',
    'receipt_logo_tip'          => 'Consejo: subí una versión horizontal con fondo transparente para que se vea bien en el ticket.',

    // Modules config
    'modules_title'             => 'Módulos del Panel',
    'modules_subtitle'          => 'Personalizá qué módulos ves en tu sidebar',
    'modules_info'              => 'Seleccioná solo los módulos que usás en tu negocio. Los módulos fijos (Dashboard, Ventas, Métodos de Pago, Stock, Gastos, Configuración y Soporte) siempre estarán disponibles.',
    'module_visible'            => 'Visible',
    'module_hidden'             => 'Oculto',
    'modules_selected_suffix'   => 'módulos seleccionados',
    'of_label'                  => 'de',
    'save_changes_btn'          => 'Guardar cambios',

    // Generic
    'saving'                    => 'Guardando...',

    // App logo form
    'logo_no_logo'      => 'Sin logo',
    'logo_current'      => 'Logo actual',
    'logo_hint'         => 'Recomendado: PNG con fondo transparente, mínimo 128×128.',
    'logo_uploading'    => 'Subiendo…',
    'logo_save_btn'     => 'Guardar logo',
    'logo_remove_btn'   => 'Quitar logo',

    // Profile page
    'profile_heading'          => 'Mi perfil',
    'profile_page_title'       => 'Configuración de la cuenta',
    'profile_page_subtitle'    => 'Gestiona tu información personal, seguridad y preferencias de la app.',
    'profile_personal_title'   => 'Información personal',
    'profile_personal_sub'     => 'Nombre, email y foto de perfil.',
    'profile_password_title'   => 'Seguridad: Contraseña',
    'profile_password_sub'     => 'Actualiza tu contraseña periódicamente.',
    'profile_2fa_title'        => 'Doble factor (2FA)',
    'profile_2fa_sub'          => 'Protege tu cuenta con un segundo paso de verificación.',
    'profile_logo_title'       => 'Personalización: Logo de la aplicación',
    'profile_logo_sub'         => 'Sube un logo para el encabezado y el sidebar.',
    'profile_sessions_title'   => 'Sesiones del navegador',
    'profile_sessions_sub'     => 'Cierra otras sesiones activas.',
    'profile_delete_title'     => 'Eliminar cuenta',
    'profile_delete_sub'       => 'Borrará de forma permanente tus datos.',

    // Payment method selector
    'pm_select_title'   => 'Métodos de Pago',
    'pm_select_aria'    => 'Seleccionar :name',
    'pm_no_methods'     => 'No hay métodos de pago configurados',
    'pm_no_methods_hint'=> 'Ve a <a href=":url" class="underline hover:text-amber-900 dark:hover:text-amber-100 font-semibold">Métodos de Pago</a> para agregar algunos.',
];
