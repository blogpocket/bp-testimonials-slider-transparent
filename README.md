# BP Testimonials Slider

Plugin para WordPress que añade un tipo de contenido **Testimonios** y permite mostrarlos en un carrusel automático con controles manuales mediante shortcode.

## Instalación

1. Sube el ZIP desde **Plugins -> Añadir nuevo -> Subir plugin**.
2. Activa el plugin.
3. Ve a **Testimonios -> Añadir nuevo** y crea varios testimonios.
   - **Título**: nombre de la persona/empresa.
   - **Contenido**: el texto del testimonio.
   - **Imagen destacada (opcional)**: avatar o foto de la persona.
4. Configura el aspecto en **Testimonios -> Ajustes**.

## Shortcodes

- `[testimonios_slider]`
- `[bp_testimonials_slider]`

Atributos opcionales:

- `posts_per_page`: número de testimonios a mostrar (por defecto `-1`, todos).
- `order`: `DESC` o `ASC` (por defecto `DESC`).

Ejemplo:

`[testimonios_slider posts_per_page="10" order="DESC"]`

## Ajustes disponibles

- **Imagen de fondo**: sube/selecciona una imagen desde la librería de medios.
- **Tamaño de fuente (px)**: tamaño del texto del testimonio (entre 10 y 60).

## Accesibilidad y comportamiento

- El carrusel avanza automáticamente y se puede controlar con botones **Anterior/Siguiente** y puntos de navegación.
- Se pausa al pasar el ratón por encima o cuando el carrusel tiene el foco.
- Soporta teclado: flechas izquierda/derecha cuando el carrusel está enfocado.

## Compatibilidad

- No usa librerías externas (vanilla JS/CSS).
- Funciona con temas clásicos y de bloques.

## Licencia

GPL-2.0-or-later


## Nota sobre diseño

- El carrusel se renderiza con **fondo transparente** para que pueda colocarse encima de una imagen existente del tema/maquetador.
- Por defecto el texto es blanco con **sombra** para mejorar la legibilidad sobre fondos con detalle.
