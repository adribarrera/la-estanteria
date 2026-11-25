# Recursos multimedia

Aquí apuntamos de dónde sale cada archivo multimedia de La Estantería, quién es su autor, con qué licencia lo usamos y con qué herramienta lo hemos editado.

## Portadas de los libros

Todas las portadas las he montado en GIMP a partir de una plantilla propia: una foto de Pexels arriba y, debajo, una franja con el título y el autor. Cada foto se ha recortado y encuadrado a 400 × 600 px y se ha exportado en WebP para que pese poco.

| Archivo | Fuente | Autor de la foto | Licencia | Herramienta |
|---|---|---|---|---|
| `img/dune_portada.webp` | Pexels | No se anotó al descargarla (ver nota) | Licencia Pexels | GIMP |
| `img/nombre_viento_portada.webp` | [Pexels](https://www.pexels.com/photo/8757601/) | Mohammed Yousif | Licencia Pexels | GIMP |
| `img/don_quijote_portada.webp` | [Pexels](https://www.pexels.com/photo/6007183/) | Huys Photography | Licencia Pexels | GIMP |
| `img/frankenstein_portada.webp` | [Pexels](https://www.pexels.com/photo/12889586/) | anish | Licencia Pexels | GIMP |
| `img/orgullo_prejuicio_portada.webp` | [Pexels](https://www.pexels.com/photo/20792102/) | Maurits Bausenhart | Licencia Pexels | GIMP |
| `img/1984_portada.webp` | [Pexels](https://www.pexels.com/photo/2035214/) | Fotios Photos | Licencia Pexels | GIMP |
| `img/cien_anos_soledad_portada.webp` | [Pexels](https://www.pexels.com/photo/34279818/) | soubhik96 | Licencia Pexels | GIMP |
| `img/el_hobbit_portada.webp` | [Pexels](https://www.pexels.com/photo/39041200/) | eduraw | Licencia Pexels | GIMP |
| `img/dracula_portada.webp` | [Pexels](https://www.pexels.com/photo/5771257/) | Danut Chirtes | Licencia Pexels | GIMP |
| `img/principito_portada.webp` | [Pexels](https://www.pexels.com/photo/40191130/) | Primitive Spaces (foto de un mural) | Licencia Pexels | GIMP |
| `img/estudio_escarlata_portada.webp` | [Pexels](https://www.pexels.com/photo/14510637/) | Knight Bits | Licencia Pexels | GIMP |
| `img/sombra_viento_portada.webp` | [Pexels](https://www.pexels.com/photo/18286195/) | clarabdiab | Licencia Pexels | GIMP |
| `img/fahrenheit_451_portada.webp` | [Pexels](https://www.pexels.com/photo/5711834/) | Anna Shvets | Licencia Pexels | GIMP |
| `img/dorian_gray_portada.webp` | [Pexels](https://www.pexels.com/photo/7127838/) | Jose Melo | Licencia Pexels | GIMP |
| `img/isla_tesoro_portada.webp` | [Pexels](https://www.pexels.com/photo/9384584/) | Leticia Azevedo | Licencia Pexels | GIMP |
| `img/rimas_leyendas_portada.webp` | [Pexels](https://www.pexels.com/photo/27773645/) | seymasungr | Licencia Pexels | GIMP |

Los autores de las fotos se han sacado de la página o del nombre de archivo que da Pexels al descargar. En la foto de Dune no se guardó el autor; como la Licencia Pexels no obliga a citarlo, se puede usar igualmente.

Las portadas no copian las de ninguna editorial: son diseños nuestros hechos con fotos libres. La de El Principito usa la foto de un mural pintado en la calle, no una ilustración del libro.

## Audio

| Archivo | Fuente | Autoría | Licencia | Herramienta |
|---|---|---|---|---|
| `audio/audiolibroorgulloyprejuicio_muestra.mp3` | LibriVox, *Orgullo y prejuicio* (Vol. 1), capítulo I | Jane Austen; traducción de José Jordán de Urríes y Azara; leído por Victor Villarraza | Dominio público | FFmpeg |
| `audio/audiolibro_frankenstein.mp3` | [LibriVox](https://librivox.org/frankenstein-el-moderno-prometeo-by-mary-wollstonecraft-shelley/), *Frankenstein (El moderno Prometeo)*, Cartas I-III | Mary Shelley; traducción de L. Costarias; leído por Victor Villarraza | Dominio público | FFmpeg |

**Orgullo y prejuicio.** Es el comienzo del primer capítulo. Con FFmpeg le quité la presentación de LibriVox, lo dejé en unos 45 segundos, le puse fundidos al principio y al final, igualé el volumen y lo pasé a mono a 96 kbps. El comando fue este:

```
ffmpeg -ss 42.8 -to 87.6 -i capitulo1.mp3 -af "afade=t=in:d=1,afade=t=out:st=42.8:d=2,loudnorm" -ac 1 -b:a 96k audiolibroorgulloyprejuicio_muestra.mp3
```

**Frankenstein.** Es el comienzo de la primera carta del capitán Walton a su hermana. Le hice lo mismo: quité los 20 primeros segundos de presentación de LibriVox, lo corté en una pausa del lector para que quedara en unos 46 segundos y le apliqué los mismos fundidos, el volumen igualado y el paso a mono a 96 kbps:

```
ffmpeg -ss 20.3 -to 66.4 -i frankensteinelmodernoprometeo_01_shelley_128kb.mp3 -af "afade=t=in:d=1,afade=t=out:st=44.1:d=2,loudnorm" -ac 1 -b:a 96k audiolibro_frankenstein.mp3
```

## Vídeo

| Archivo | Fuente | Autor | Licencia | Herramienta |
|---|---|---|---|---|
| `video/booktrailer.mp4` (imagen) | [Pexels 4860897](https://www.pexels.com/video/4860897/) y [Pexels 5381802](https://www.pexels.com/video/5381802/), más nuestras portadas, textos y cierre | Indicado en cada enlace de Pexels; resto, equipo La Estantería | Licencia Pexels y diseño propio | FFmpeg y Python (Pillow) |
| `video/booktrailer.mp4` (música) | Pixabay, pista 216331 (`clavier-music-fur-elise-beethoven-216331.mp3`): *Para Elisa* (Beethoven) | Grabación de [Clavier-Music](https://pixabay.com/users/clavier-music-16027823/); obra de Ludwig van Beethoven | Licencia de contenido de Pixabay (grabación) y dominio público (obra) | FFmpeg |
| `video/booktrailer_poster.webp` | Cierre del propio booktrailer | Equipo La Estantería | Diseño propio | Python (Pillow) |

El booktrailer es un pequeño anuncio de la app: no solo enseña libros, sino que invita al usuario a crear su estantería. Dura 24 segundos, está a 1280 × 720 y pesa 2,2 MB (H.264 + audio AAC a 128 kbps).

| Tiempo | Imagen | Texto |
|---|---|---|
| 0-4 s | Una mano recorre la estantería | ¿Y ahora qué leo? |
| 4-8 s | Saca un libro | Guarda lo que quieres leer + etiqueta "Pendiente" |
| 9-11,5 s | Una lectora pasa las páginas | Sigue tu lectura + etiqueta "Leyendo" |
| 12-14,5 s | Sigue leyendo | Comparte lo que sientes + cinco estrellas |
| 15-19 s | Nuestras portadas van cayendo en dos baldas y queda un hueco con un "+" | Tu club de lectura, en un solo lugar |
| 19-24 s | Cierre crema con "La Estantería"; una pluma de ave escribe "Tu club de lectura personal" y aparece el botón "Empieza tu estantería" | |

Los textos van en tarjetas crema con una franja terracota, y las etiquetas de estado y las estrellas tienen el mismo aspecto que en la web, para que el usuario las reconozca al entrar. El hueco con el "+" en la estantería es una invitación a añadir su propio libro. El clip de la lectora venía en vertical y lo recortamos a horizontal, centrado en el libro. La pluma y la letra caligráfica (Great Vibes, licencia OFL) son las mismas que las del banner.

La música es *Para Elisa* de Beethoven, en una grabación de piano de [Clavier-Music](https://pixabay.com/users/clavier-music-16027823/) publicada en Pixabay. La adaptamos con FFmpeg para que encaje con el vídeo: empieza con el motivo conocido desde el primer segundo; a los 7 segundos se salta a la repetición del tema con un fundido cruzado de 80 ms para que el corte no se note; se acelera un 6 % sin cambiar el tono para que el acorde de cierre de la frase caiga justo en el segundo 19, cuando aparece "La Estantería"; después suena el inicio de la parte en tono mayor mientras la pluma escribe y aparece el botón, y termina con un fundido de 3 segundos. También igualamos el volumen. Comandos:

```
ffmpeg -i clavier-music-fur-elise-beethoven-216331.mp3 -filter_complex "[0:a]atrim=start=0.05:end=7.10,asetpts=PTS-STARTPTS[a1];[0:a]atrim=start=14.80:end=34.5,asetpts=PTS-STARTPTS[a2];[a1][a2]acrossfade=d=0.08,atempo=1.06,atrim=0:24,afade=t=in:d=0.3,afade=t=out:st=21:d=3,loudnorm=I=-18:TP=-1.5" musica.wav
ffmpeg -i booktrailer_sin_musica.mp4 -i musica.wav -map 0:v -map 1:a -c:v copy -c:a aac -b:a 128k -shortest -movflags +faststart booktrailer.mp4
```

El póster es el cierre completo con el botón, en WebP sin pérdida (13 KB). Así, quien no le dé al play ve igualmente la invitación. Como el vídeo tiene sonido, en la web debe ir con `controls`; si se quiere reproducción automática, tiene que ser con `autoplay muted loop playsinline`.

Montaje de la imagen con FFmpeg (los textos, el catálogo y el cierre son secuencias de imágenes generadas con Pillow):

```
ffmpeg -ss 0 -t 9 -i 4860897-uhd_4096_2160_25fps.mp4 -ss 1.5 -t 7 -i 5381802-uhd_2160_4096_30fps.mp4 -framerate 25 -i textos/%04d.png -framerate 25 -i catalogo/%04d.png -framerate 25 -i cierre/%04d.png -filter_complex "[0:v]scale=-2:720,crop=1280:720,fps=25,setsar=1,format=yuv420p[a];[1:v]crop=2160:1215:0:880,scale=1280:720,fps=25,setsar=1,format=yuv420p[b];[a][b]xfade=transition=fade:duration=1:offset=8,fade=t=in:st=0:d=0.6[ab];[ab][2:v]overlay=0:0:eof_action=pass,format=yuv420p[abo];[abo][3:v]xfade=transition=fade:duration=1:offset=14[abc];[abc][4:v]xfade=transition=fade:duration=1:offset=18,format=yuv420p[v]" -map "[v]" -c:v libx264 -preset veryfast -crf 26 -pix_fmt yuv420p -movflags +faststart booktrailer_sin_musica.mp4
```

## Logo

| Archivo | Uso | Autoría | Licencia | Herramienta |
|---|---|---|---|---|
| `logo/la-estanteria-libro.svg` | Dibujo del logo: un libro abierto con marcapáginas (595 bytes) | Equipo La Estantería | Diseño propio | Figma (diseño) y código SVG escrito a mano |
| `logo/favicon.svg` | Icono de la pestaña del navegador (402 bytes) | Equipo La Estantería | Diseño propio | Figma (diseño) y código SVG escrito a mano |

El logo es un libro abierto con un marcapáginas terracota. Lo diseñamos en Figma partiendo de tres propuestas (libro abierto, lomos en la balda y libro con pluma) y elegimos el libro abierto porque es el que mejor se reconoce en pequeño. Después escribimos el SVG a mano con trazados vectoriales (`<path>` con coordenadas relativas) para dejarlo lo más ligero posible: menos de 1 KB, sin imágenes, sin fuentes y sin metadatos. Se ve nítido a cualquier tamaño.

El nombre "La Estantería" y el lema "Club de lectura" no van dentro del SVG, sino como texto HTML con CSS al lado del dibujo. Así el logo pesa mucho menos, el texto se puede seleccionar, lo leen los buscadores y los lectores de pantalla, y usa la misma tipografía que el resto de la web.

Los colores del SVG son variables CSS (`--logo-tinta`, `--logo-acento`) con los colores del tema claro como valor por defecto, de modo que el logo cambia solo con el tema oscuro. El favicon es una versión simplificada (sin las líneas de texto y con trazo más grueso) para que se distinga a 16 px, y cambia a colores claros si el navegador está en modo oscuro. Todos los navegadores actuales admiten favicon en SVG, así que no hace falta versión PNG.

Para que el logo cambie con el tema y se anime, el SVG se pega en línea dentro del enlace de la cabecera (como `<img>`, el CSS de la página no le llega):

```html
<link rel="icon" href="assets/media/logo/favicon.svg" type="image/svg+xml">

<a class="logo" href="index.php" aria-label="La Estantería, ir al catálogo">
  <!-- contenido de assets/media/logo/la-estanteria-libro.svg, cambiando role="img" aria-label="La Estantería" por aria-hidden="true" -->
  <span class="logo__nombre">
    <span class="logo__titulo">La Estantería</span>
    <span class="logo__lema">Club de lectura</span>
  </span>
</a>
```

```css
:root { --logo-tinta: #2B2622; --logo-acento: #B5532E; }
.tema-oscuro { --logo-tinta: #F4EDE1; --logo-acento: #D9774F; }
.logo { display: inline-flex; align-items: center; gap: 14px; text-decoration: none; border-radius: 8px; }
.logo svg { width: 64px; height: 64px; flex: none; }
.logo__nombre { display: flex; flex-direction: column; gap: 4px; }
.logo__titulo { font-family: "Libre Baskerville", Georgia, serif; font-weight: 700; font-size: 1.9rem; line-height: 1; color: var(--logo-tinta); }
.logo__lema { font-family: Inter, system-ui, sans-serif; font-weight: 600; font-size: 0.72rem; letter-spacing: 0.3em; text-transform: uppercase; color: var(--logo-acento); }
.logo .le-libro, .logo .le-marcapaginas { transform-box: fill-box; transition: transform 0.3s ease; }
.logo .le-libro { transform-origin: center bottom; }
.logo:hover .le-marcapaginas, .logo:focus-visible .le-marcapaginas { transform: translateY(5px); }
.logo:hover .le-libro, .logo:focus-visible .le-libro { transform: scale(1.04); }
.logo:focus-visible { outline: 3px solid var(--logo-acento); outline-offset: 4px; }
@media (prefers-reduced-motion: reduce) { .logo .le-libro, .logo .le-marcapaginas { transition: none; } }
```

Al pasar el ratón o al llegar con el teclado, el marcapáginas baja un poco y el libro crece ligeramente; con "reducir movimiento" activado no se anima. La clase `tema-oscuro` es la que se ponga en `<body>` o `<html>` al pulsar el botón de tema; si el equipo usa otro nombre, solo hay que cambiarlo aquí. Libre Baskerville e Inter se pueden cargar desde Google Fonts; si no se cargan, el título usa Georgia y el lema la fuente del sistema.

## Animación

> **Pendiente de decidir.** El banner se mantiene de momento en el proyecto mientras el equipo decide si se queda como animación del proyecto (el enunciado pide "una animación breve a partir de imágenes fijas") o se sustituye por otra opción más ligera.

| Archivo | Fuente | Autoría | Licencia | Herramienta |
|---|---|---|---|---|
| `animacion/banner_firma.webp` | Diseño propio | Equipo La Estantería | Diseño propio; fuente [Great Vibes](https://fonts.google.com/specimen/Great+Vibes) con licencia SIL Open Font License 1.1 | Python (Pillow) |
| `animacion/banner_firma_final.webp` | Último fotograma del banner | Equipo La Estantería | Igual que el banner | Python (Pillow) |

Es un banner de 1200 × 300 en el que una pluma de ave firma "La Estantería" en letra caligráfica. La pluma entra desde arriba, escribe la firma de izquierda a derecha siguiendo el trazo de las letras, hace una rúbrica en terracota debajo y se va mientras aparece "Club de lectura". Dura unos 5 segundos, se reproduce una sola vez (WebP animado con `loop=1`) y se queda con la firma terminada. Usa los colores de las portadas y pesa unos 260 KB. La pluma está dibujada por nosotros, sin fotos ni material de terceros.

`banner_firma_final.webp` es la imagen fija del resultado final (12 KB, sin pérdida), para quien tenga activada la opción de reducir movimiento (`prefers-reduced-motion`).

## Sobre las licencias

- **Licencia Pexels:** las fotos se pueden usar gratis, también modificadas, y no es obligatorio citar al autor, aunque aquí lo hacemos igualmente.
- **Licencia de contenido de Pixabay:** la grabación de *Para Elisa* se puede usar, editar y adaptar gratis, sin obligación de atribución; aun así citamos al intérprete. La obra de Beethoven es de dominio público.
- **Dominio público:** las grabaciones de LibriVox no tienen derechos de autor y se pueden usar libremente.
- **SIL Open Font License (OFL):** las fuentes Great Vibes (letra caligráfica del booktrailer), Libre Baskerville e Inter (propuestas para el nombre junto al logo) son libres. Se puede usar, modificar e incluir en el proyecto; lo único que no se permite es venderla por separado.
