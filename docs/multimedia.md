# Recursos multimedia

Aquí apuntamos de dónde sale cada archivo multimedia de La Estantería, quién es su autor, con qué licencia lo usamos y con qué herramienta lo hemos editado.

## Portadas de los libros

Todas las portadas las he montado en GIMP a partir de una plantilla propia: una foto de Pexels arriba y, debajo, una franja con el título y el autor. Cada foto se ha recortado y encuadrado a 400 × 600 px y se ha exportado en WebP para que pese poco.

| Archivo | Fuente | Autor de la foto | Licencia | Herramienta |
|---|---|---|---|---|
| `img/dune_portada.webp` | Pexels | _pendiente_ | Licencia Pexels | GIMP |
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
| `video/booktrailer.mp4` | [Pexels 4860897](https://www.pexels.com/video/4860897/) y [Pexels 5381802](https://www.pexels.com/video/5381802/) | _pendiente_ | Licencia Pexels | FFmpeg |
| `video/booktrailer_poster.webp` | Rótulo final del propio booktrailer | Equipo La Estantería | Diseño propio | FFmpeg |

El booktrailer dura 19 segundos y une dos clips de Pexels: una mano que elige un libro de la estantería y una lectora pasando las páginas. Al final lleva un rótulo nuestro con "La Estantería" y "Club de lectura" en los colores de las portadas. Está a 1280 × 720, sin audio y comprimido en H.264 para que cargue rápido (1,5 MB). El clip de la lectora venía en vertical, así que lo recorté a formato horizontal centrado en el libro. El póster, que se muestra mientras carga el vídeo, es el rótulo final del booktrailer, así que el vídeo empieza y termina con la misma imagen del club. El póster lo generé aparte con el mismo diseño, directamente a 1280 × 720 y en WebP sin pérdida, para que el texto se vea nítido (el fotograma sacado del vídeo arrastraba la compresión). Pesa unos 6 KB. El vídeo lo monté con el primer comando y el póster con el segundo:

```
ffmpeg -ss 0 -t 9 -i 4860897-uhd_4096_2160_25fps.mp4 -ss 1.5 -t 7 -i 5381802-uhd_2160_4096_30fps.mp4 -f lavfi -t 5 -i "color=c=0xF4EDE1:s=1280x720:r=25" -filter_complex "[0:v]scale=-2:720,crop=1280:720,fps=25,setsar=1,format=yuv420p[a];[1:v]crop=2160:1215:0:880,scale=1280:720,fps=25,setsar=1,format=yuv420p[b];[2:v]drawbox=x=540:y=402:w=200:h=3:color=0xB5532E:t=fill,drawtext=fontfile=LiberationSerif-Bold.ttf:text='La Estantería':fontsize=72:fontcolor=0x2B2622:x=(w-text_w)/2:y=290,drawtext=fontfile=LiberationSans-Regular.ttf:text='C L U B   D E   L E C T U R A':fontsize=22:fontcolor=0xB5532E:x=(w-text_w)/2:y=430,setsar=1,format=yuv420p[c];[a][b]xfade=transition=fade:duration=1:offset=8[ab];[ab][c]xfade=transition=fade:duration=1:offset=14,fade=t=in:st=0:d=0.6,format=yuv420p[v]" -map "[v]" -an -c:v libx264 -preset veryfast -crf 26 -pix_fmt yuv420p -movflags +faststart booktrailer.mp4
ffmpeg -f lavfi -i "color=c=0xF4EDE1:s=1280x720:r=1" -frames:v 1 -vf "drawbox=x=540:y=402:w=200:h=3:color=0xB5532E:t=fill,drawtext=fontfile=LiberationSerif-Bold.ttf:text='La Estantería':fontsize=72:fontcolor=0x2B2622:x=(w-text_w)/2:y=290,drawtext=fontfile=LiberationSans-Regular.ttf:text='C L U B   D E   L E C T U R A':fontsize=22:fontcolor=0xB5532E:x=(w-text_w)/2:y=430,format=rgb24" -c:v libwebp -lossless 1 -compression_level 6 booktrailer_poster.webp
```

## Animación

_Pendiente._

## Sobre las licencias

- **Licencia Pexels:** las fotos se pueden usar gratis, también modificadas, y no es obligatorio citar al autor, aunque aquí lo hacemos igualmente.
- **Dominio público:** las grabaciones de LibriVox no tienen derechos de autor y se pueden usar libremente.
