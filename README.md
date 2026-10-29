# Laboratorio 09 Grupal — Sistema de Registro de Contacto (PHP & JSP)

**Asignatura:** Desarrollo de Aplicaciones Web (IS093A) · Unidad II — Desarrollo Web Fullstack
**Tema:** Arquitectura cliente-servidor, servidores web, PHP y JSP, ciclo de vida y despliegue.

## Integrantes y roles

| Rol | Integrante | Responsabilidad |
|---|---|---|
| Arquitecto de Despliegue | *(nombre)* | Configuración XAMPP/Tomcat, rutas, puertos, logs |
| Desarrollador PHP | Ingrid Andrade | `$_POST`, validación, `session_start()`, `htmlspecialchars` |
| Desarrollador JSP | *(nombre)* | `request.getParameter()`, session, JSTL / web.xml |
| QA & Documentador | *(nombre)* | Pruebas HTTP, validación de seguridad, matriz comparativa, README |

---

## Estructura del proyecto

```
lab09-grupal/
├── php-app/                    → copiar a htdocs/ (Apache)
│   ├── index.html              formulario de contacto
│   └── registro.php            valida, guarda en $_SESSION y muestra tabla
├── jsp-app/                    → copiar a Tomcat/webapps/
│   ├── index.jsp               formulario de contacto
│   ├── registro.jsp            versión con JSTL (<c:forEach> + <c:out>)
│   ├── registro-scriptlet.jsp  versión alterna sin JSTL (plan B)
│   └── WEB-INF/
│       └── web.xml
└── README.md
```

---

## Cómo ejecutar

### PHP (XAMPP)
1. Inicia **Apache** desde el panel de XAMPP.
2. Copia `php-app/` a `C:\xampp\htdocs\` y renómbrala `lab09`.
3. Abre `http://localhost/lab09/index.html`.

### JSP (Tomcat 10.x)
1. Inicia **Tomcat** (`startup.bat` o desde XAMPP).
2. Copia `jsp-app/` a `C:\xampp\tomcat\webapps\` y renómbrala `lab09`.
3. Abre `http://localhost:8080/lab09/index.jsp`.

> **Nota sobre JSTL:** `registro.jsp` usa JSTL (`<c:forEach>`). En Tomcat 10+ requiere
> añadir las librerías `jakarta.servlet.jsp.jstl-api` y su implementación en
> `WEB-INF/lib/`. Si no quieres configurarlas, usa `registro-scriptlet.jsp`
> (misma funcionalidad sin JSTL): cambia el `action` del formulario en `index.jsp`
> a `registro-scriptlet.jsp`.

---

## Diagrama de flujo HTTP (Cliente → Servidor → Backend → Respuesta)

```
┌──────────┐   1. URL / submit    ┌──────────┐
│ CLIENTE  │ ───────────────────► │   DNS    │  Resuelve localhost → 127.0.0.1
│(Navegador)│                     └────┬─────┘
└──────────┘                          │
     ▲                                ▼
     │                          ┌──────────┐
     │                          │  TCP/IP  │  Abre conexión al puerto (80 PHP / 8080 JSP)
     │                          └────┬─────┘
     │                               ▼
     │                        ┌─────────────┐
     │                        │ HTTP REQUEST│  POST /registro.php  (nombre, correo, mensaje)
     │                        └──────┬──────┘
     │                               ▼
     │                     ┌───────────────────┐
     │                     │  SERVER (Apache/  │  Recibe la petición
     │                     │     Tomcat)       │
     │                     └─────────┬─────────┘
     │                               ▼
     │                     ┌───────────────────┐
     │                     │     BACKEND       │  Valida método (POST), sanitiza,
     │                     │  (PHP / Servlet)  │  guarda en sesión, arma la tabla
     │                     └─────────┬─────────┘
     │                               ▼
     │                     ┌───────────────────┐
     │   4. HTTP RESPONSE  │  RESPONSE (200 OK)│  HTML con la tabla de registros
     └─────────────────────┤   + Render HTML   │
                           └───────────────────┘
```

**Códigos HTTP manejados:** `200 OK` (éxito), `405 Method Not Allowed` (si se
accede por GET en lugar de POST).

---

## Matriz comparativa: PHP vs JSP

| Aspecto | PHP | JSP |
|---|---|---|
| **Lenguaje** | PHP (interpretado) | Java (compilado a servlet) |
| **Ciclo de vida** | parse → execute → output en cada request | traducción → compilación → instanciación → service → destroy |
| **Primera ejecución** | Interpreta el script en cada petición | Se traduce a `.java`, se compila a servlet una vez y queda cargado |
| **Servidor** | Apache + módulo PHP | Servlet container: Apache Tomcat |
| **Puerto típico** | 80 | 8080 |
| **Gestión de estado** | `$_SESSION` (archivos de sesión del servidor) | `session.setAttribute()` (objeto HttpSession en memoria) |
| **Gestión de memoria** | Proceso corto: libera al terminar la petición | El servlet vive en el container; reutiliza instancia entre peticiones |
| **Despliegue** | Copiar archivos a `htdocs/` (inmediato) | Empaquetar `.war` o carpeta en `webapps/` |
| **Escalabilidad** | Escala horizontal fácil, stateless entre requests | Buen rendimiento tras compilar; usa hilos del container |
| **Madurez del ecosistema** | Enorme en web (CMS, frameworks como Laravel) | Fuerte en entornos empresariales Java (Spring, Jakarta EE) |

### Conclusión
Ambas tecnologías son **server-side** (SSR) sobre HTTP, que es **stateless** — por eso
el estado (los contactos) se conserva con mecanismos de **sesión**. PHP destaca por su
simplicidad y despliegue inmediato; JSP, al compilarse a servlet y vivir en el container,
ofrece rendimiento sostenido e integración con el ecosistema Java.

---

## Validaciones de seguridad implementadas

- **Validación de método:** solo se acepta `POST`; un `GET` directo devuelve **405**.
- **Sanitización de entradas:** `filter_input()` en PHP; validación con regex en JSP.
- **Prevención de XSS:** `htmlspecialchars()` en PHP y `<c:out>` / escape manual en JSP,
  de modo que una entrada como `<script>alert(1)</script>` se muestra como texto y no se ejecuta.
- **Validación de campos:** nombre y mensaje obligatorios, correo con formato válido.

## Evidencias a incluir (capturas)

1. XAMPP con Apache y Tomcat iniciados (servicios en verde).
2. Formulario PHP y tabla de registros acumulados.
3. Formulario JSP y tabla de registros acumulados.
4. DevTools → Network: status `200 OK` y la respuesta `405` al forzar un GET.
5. Logs: `access.log` / `error.log` (Apache) y `catalina.out` (Tomcat).
6. Estructura de carpetas `php-app/` y `jsp-app/`.
