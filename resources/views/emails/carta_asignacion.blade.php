<p>Hola {{ $asignacion->empleado->nombre_completo }},</p>

<p>
Se te ha asignado un equipo de cómputo por parte del área de TI.
</p>

<ul>
    <li><strong>Número de empleado:</strong> {{ $asignacion->empleado->numero_empleado }}</li>
    <li><strong>Correo:</strong> {{ $asignacion->empleado->correo }}</li>
    <li><strong>Equipo:</strong> {{ $asignacion->equipo->codigo_interno }} - {{ $asignacion->equipo->marca }} {{ $asignacion->equipo->modelo }}</li>
</ul>

<p>
Adjunto encontrarás la <strong>carta responsiva en PDF</strong>.
</p>

<p>
Saludos,<br>
<strong>Departamento de TI</strong>
</p>



