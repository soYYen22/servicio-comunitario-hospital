<?php

return [

    // Todas las secciones de la página de configuración
    'sections' => [
        'app' => [
            'title' => 'Configuración General',
            'descriptions' => '', // (opcional)
            'icon' => 'fa fa-cog', // (opcional)

            'inputs' => [
                [
                    'name' => 'app_name', // clave única para el ajuste
                    'type' => 'text', // tipo de entrada (text, number, textarea, etc.)
                    'label' => 'Nombre de la Aplicación', // etiqueta del campo
                    // propiedades opcionales
                    'placeholder' => 'Nombre de la Aplicación', // texto de marcador
                    'class' => 'form-control', // clase CSS
                    'style' => '', // estilos en línea
                    'rules' => 'required|min:2|max:20', // validación
                    'value' => config('app.name'), // valor por defecto
                    'hint' => 'Puedes establecer aquí el nombre de la aplicación' // texto de ayuda
                ],
                [
                    'name' => 'app_currency',
                    'type' => 'text',
                    'label' => 'Moneda',
                    'placeholder' => 'Símbolo de la moneda',
                    'class' => 'form-control',
                    'style' => '', // estilos en línea
                    'rules' => 'required|max:10', // validación
                    'value' => '', // valor por defecto (sin símbolo)
                    'hint' => 'Usa el símbolo de tu moneda, por ejemplo $',
                ],
                [
                    'name' => 'logo',
                    'type' => 'image',
                    'label' => 'Subir Logo',
                    'hint' => 'Tamaño recomendado de la imagen: 150px x 150px',
                    'rules' => 'image|max:500',
                    'disk' => 'public', // disco donde se guarda
                    'path' => 'logos', // ruta en el disco
                    'preview_class' => 'thumbnail',
                    'preview_style' => 'height:40px'
                ],
                [
                    'name' => 'favicon',
                    'type' => 'image',
                    'label' => 'Subir Ícono (Favicon)',
                    'hint' => 'Tamaño recomendado de la imagen: 16px x 16px o 32px x 32px',
                    'rules' => 'image|max:500',
                    'disk' => 'public',
                    'path' => 'logos',
                    'preview_class' => 'thumbnail',
                    'preview_style' => 'height:40px'
                ],
            ]
        ],
        
    ],

    // URL de la página de configuración (para GET y POST)
    'url' => 'settings',

    // Middleware a ejecutar en esta ruta
    'middleware' => ['auth'],

    // Vista utilizada para la página de configuración
    // 'setting_page_view' => 'app_settings::settings_page',
    'setting_page_view' => 'admin.settings',
    'flash_partial' => 'app_settings::_flash',

    // Clases CSS para secciones
    'section_class' => 'card mb-3',
    'section_heading_class' => 'card-header',
    'section_body_class' => 'card-body',

    // Clases CSS para los inputs
    'input_wrapper_class' => 'form-group',
    'input_class' => 'form-control',
    'input_error_class' => 'has-error',
    'input_invalid_class' => 'is-invalid',
    'input_hint_class' => 'form-text text-muted',
    'input_error_feedback_class' => 'text-danger',

    // Botón de guardar
    'submit_btn_text' => 'Guardar Configuración',
    'submit_success_message' => 'La configuración se ha guardado correctamente.',

    // Eliminar configuraciones huérfanas (que ya no existan)
    'remove_abandoned_settings' => false,

    // Controlador que maneja la página y el guardado
    'controller' => '\App\Http\Controllers\Admin\SettingController',

    // Grupo de configuración
    'setting_group' => function() {
        // return 'user_'.auth()->id();
        return 'default';
    }
];
