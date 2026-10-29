<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" import="java.util.*" %>
<%--
  registro-scriptlet.jsp — VERSIÓN SIN JSTL (plan B).
  Igual que registro.jsp pero itera con scriptlet controlado, por si no
  quieres añadir la librería JSTL a Tomcat. La rúbrica acepta "<c:forEach>
  O <% %>". Si usas esta versión, cambia el action del formulario en
  index.jsp a "registro-scriptlet.jsp".
--%>
<%
    if (!"POST".equalsIgnoreCase(request.getMethod())) {
        response.setStatus(405);
        out.println("Método no permitido. Use el formulario (POST).");
        return;
    }

    String nombre  = request.getParameter("nombre");
    String correo  = request.getParameter("correo");
    String mensaje = request.getParameter("mensaje");

    List<String> errores = new ArrayList<>();
    if (nombre == null || nombre.trim().isEmpty())   errores.add("El nombre es obligatorio.");
    if (correo == null || !correo.matches("^[\\w.+-]+@[\\w-]+\\.[\\w.-]+$"))
        errores.add("El correo electrónico no es válido.");
    if (mensaje == null || mensaje.trim().isEmpty()) errores.add("El mensaje es obligatorio.");

    List<Map<String,String>> registros =
        (List<Map<String,String>>) session.getAttribute("registros");
    if (registros == null) {
        registros = new ArrayList<>();
        session.setAttribute("registros", registros);
    }

    if (errores.isEmpty()) {
        Map<String,String> fila = new HashMap<>();
        fila.put("nombre", nombre.trim());
        fila.put("correo", correo.trim());
        fila.put("mensaje", mensaje.trim());
        fila.put("fecha", new java.text.SimpleDateFormat("yyyy-MM-dd HH:mm:ss").format(new java.util.Date()));
        registros.add(fila);
    }

    // Escape manual anti-XSS
    pageContext.setAttribute("reg", registros);
%>
<%!
    // Función de escape reutilizable (declaración JSP)
    public String esc(String s) {
        if (s == null) return "";
        return s.replace("&","&amp;").replace("<","&lt;")
                .replace(">","&gt;").replace("\"","&quot;");
    }
%>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registros · JSP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <style>
      * { margin: 0; padding: 0; box-sizing: border-box; }
      body { font-family: "Poppins", sans-serif; min-height: 100vh; padding: 2rem 1.5rem;
        background: linear-gradient(135deg, #7f1d1d 0%, #be123c 50%, #fb7185 100%); }
      .caja { background: #fff; width: 100%; max-width: 760px; margin: 0 auto; border-radius: 20px;
        padding: 2.5rem; box-shadow: 0 25px 60px -15px rgba(0,0,0,.4); }
      .cabecera { text-align: center; margin-bottom: 1.5rem; }
      .emoji { font-size: 2.4rem; }
      h1 { font-size: 1.4rem; color: #1e293b; margin-top: .3rem; }
      .desc { color: #94a3b8; font-size: .85rem; margin-top: .25rem; }
      .lista-err { list-style: none; margin-bottom: 1.5rem; }
      .lista-err li { padding: .75rem 1rem; background: #fef2f2; border-left: 3px solid #ef4444;
        border-radius: 8px; margin-bottom: .5rem; color: #b91c1c; font-size: .88rem; }
      .contador { display: inline-block; background: #ffe4e6; color: #be123c; font-size: .78rem;
        font-weight: 600; padding: .3rem .9rem; border-radius: 999px; margin-bottom: 1rem; }
      table { width: 100%; border-collapse: collapse; font-size: .88rem; }
      thead th { background: #be123c; color: #fff; text-align: left; padding: .75rem .9rem; font-weight: 600; }
      tbody td { padding: .7rem .9rem; border-bottom: 1px solid #eef2f7; color: #334155; }
      tbody tr:nth-child(even) { background: #f8fafc; }
      .vacio { text-align: center; color: #94a3b8; padding: 2rem; }
      .volver { display: block; text-align: center; margin-top: 1.5rem; padding: .85rem; border-radius: 12px;
        background: #f1f5f9; color: #334155; text-decoration: none; font-weight: 600; font-size: .9rem; }
    </style>
  </head>
  <body>
    <div class="caja">
      <div class="cabecera">
        <div class="emoji">📇</div>
        <h1>Registros de Contacto (JSP)</h1>
        <p class="desc">Almacenados en <strong>session</strong> del contenedor Tomcat</p>
      </div>

      <% if (!errores.isEmpty()) { %>
        <ul class="lista-err">
          <% for (String err : errores) { %>
            <li><%= esc(err) %></li>
          <% } %>
        </ul>
      <% } %>

      <span class="contador">Total de registros: <%= registros.size() %></span>

      <% if (registros.isEmpty()) { %>
        <p class="vacio">Aún no hay contactos registrados.</p>
      <% } else { %>
        <table>
          <thead>
            <tr><th>#</th><th>Nombre</th><th>Correo</th><th>Mensaje</th><th>Fecha</th></tr>
          </thead>
          <tbody>
            <% int i = 1; for (Map<String,String> r : registros) { %>
              <tr>
                <td><%= i++ %></td>
                <td><%= esc(r.get("nombre")) %></td>
                <td><%= esc(r.get("correo")) %></td>
                <td><%= esc(r.get("mensaje")) %></td>
                <td><%= esc(r.get("fecha")) %></td>
              </tr>
            <% } %>
          </tbody>
        </table>
      <% } %>

      <a class="volver" href="index.jsp">← Registrar otro contacto</a>
    </div>
  </body>
</html>
