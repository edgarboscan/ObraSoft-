# 🏗️ ObraSoft - Control de Gastos y Pago a Obreros

**ObraSoft** es un sistema de gestión diseñado específicamente para la industria de la construcción y proyectos por obra. Su objetivo es simplificar y transparentar el control financiero de las obras, permitiendo a capataces, constructores y contratistas gestionar de manera eficiente tanto los **gastos de materiales/insumos** como el **pago de jornales a obreros**.

## ✨ Características Principales

- **💰 Control de Gastos de Obra:** Registra y categoriza todos los gastos asociados a una obra específica (materiales, alquiler de maquinaria, servicios, etc.).
- **👥 Gestión de Obreros y Jornales:** Administra un directorio de obreros y controla su asistencia, días trabajados o tareas realizadas a destajo.
- **📊 Cálculo Automático de Pagos:** Calcula de forma automática el total a pagar a cada obrero basado en jornales diarios, horas extras o trabajo por unidad (destajo).
- **📈 Visibilidad por Obra:** Visualiza de manera clara la rentabilidad de cada proyecto, comparando el presupuesto estimado con los gastos reales y la mano de obra.
- **🧾 Historial y Reportes:** Genera reportes de pagos y gastos para mantener un historial financiero ordenado, facilitando la rendición de cuentas.
- **🔒 Datos Claros y Seguros:** Mantén toda la información financiera de tu obra en un solo lugar, accesible y organizada.

## 🚀 Tecnologías Utilizadas

_(Esta sección es importante, complétala con las tecnologías que uses)_

- **Frontend:** [Ej: React, Vue, HTML/CSS]
- **Backend:** [Ej: Python/Django, Node.js, PHP]
- **Base de Datos:** [Ej: PostgreSQL, MySQL, SQLite]
- **Otros:** [Ej: Docker, APIs externas, etc.]

## ⚙️ Instalación y Uso

_(Instrucciones básicas para que otros desarrolladores o tú mismo puedan poner el proyecto en marcha)_

1.  Clona el repositorio:
    ```bash
    git clone https://github.com/tu-usuario/obrasoft.git
    ```
2.  Accede al directorio del proyecto:
    ```bash
    cd obrasoft
    ```
3.  _(Añade aquí los siguientes pasos según tu stack, por ejemplo: instalar dependencias, configurar variables de entorno, correr migraciones)_
    ```bash
    # Ejemplo con Node:
    # npm install
    # npm run dev
    ```
4.  Abre tu navegador en `http://localhost:3000` (o el puerto que corresponda).

## ⚙️ Configuración del Backend (PHP)

Sigue estos pasos para poner en marcha la parte backend del proyecto:

1. Entra en la carpeta `backend`:
   ```bash
   cd backend
   ```
2. Copia el ejemplo de variables de entorno y edítalo con tus credenciales de base de datos:
   ```bash
   cp .env.example .env
   # Luego abre .env y ajusta DB_HOST, DB_NAME, DB_USER, DB_PASSWORD, DB_CHARSET, DB_PORT
   ```
3. Instala dependencias de PHP con Composer (asegúrate de tener Composer instalado):
   ```bash
   composer install
   ```
4. Crea la carpeta para imágenes (si no existe) y asegúrate que el servidor PHP tiene permisos de escritura:
   ```bash
   mkdir -p imagenes
   # En Windows PowerShell: New-Item -ItemType Directory -Path imagenes -Force
   ```
5. (Opcional) Inicia el servidor web o configura tu virtual host para que `backend/public` sea el document root.

Con esto el backend quedará listo para responder las rutas mínimas de ejemplo y usar la base de datos.

## 🤝 Cómo Contribuir

¡Las contribuciones son bienvenidas! Si tienes ideas para mejorar ObraSoft, encuentra un error o quieres añadir una nueva funcionalidad:

1.  Haz un Fork del proyecto.
2.  Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`).
3.  Haz commit de tus cambios (`git commit -m 'Añadir nueva funcionalidad'`).
4.  Haz push a la rama (`git push origin feature/nueva-funcionalidad`).
5.  Abre un Pull Request.

## 📄 Licencia

_(Indica aquí el tipo de licencia, por ejemplo: MIT, GPL-3.0, etc. Si no sabes cuál poner, "MIT" es una opción común y permisiva)._

Este proyecto está bajo la Licencia [MIT](LICENSE).

---

**ObraSoft** - Construyendo orden, gestionando tu obra. 🧱📐

xplicación Detallada de Cada Tabla en el Sistema

1. TABLAS DE SEGURIDAD Y AUTENTICACIÓN
   roles
   Propósito: Almacena los diferentes perfiles de acceso al sistema

Funcionalidad: Controla qué acciones puede realizar cada tipo de usuario

Ejemplos: Administrador (acceso total), Operador (gestión diaria), Auditor (solo consultas)

Campos clave: nivel_prioridad (1=máximo acceso), activo (para deshabilitar roles)

usuarios
Propósito: Gestiona las credenciales y datos de las personas que acceden al sistema

Funcionalidad: Autenticación, control de intentos fallidos, bloqueo de seguridad

Características: Passwords hasheados con bcrypt, control de sesiones

Seguridad: intentos_fallidos y bloqueado previenen ataques de fuerza bruta

auditoria_acciones
Propósito: Registro histórico de todas las modificaciones importantes

Funcionalidad: Trazabilidad completa de quién, cuándo y qué cambió

Uso: Para cumplimiento legal, detección de fraudes, recuperación de información

Almacenamiento: Usa JSON para guardar estados anteriores y nuevos

2. TABLAS PRINCIPALES DE OBRAS
   areas
   Propósito: Define los departamentos o áreas funcionales de la empresa

Funcionalidad: Asigna responsables y controla cupos por área

Control: cupo_maximo_mensual limita la capacidad operativa por área

Ejemplos: Construcción, Electricidad, Plomería, Acabados

tipos_obra
Propósito: Cataloga los diferentes tipos de proyectos/obras

Funcionalidad: Permite clasificar y filtrar obras por categoría

Características: requiere_permiso_especial para obras que necesitan autorización extra

Ejemplos: Obra civil, Remodelación, Instalación eléctrica, Mantenimiento

obras
Tabla más importante: Corazón del sistema de control de obras

Propósito: Almacena toda la información de cada proyecto/obra

Seguimiento:

Control de fechas (inicio, fin estimado, fin real)

Control financiero (presupuesto vs costo actual)

Estados (Planificada → En curso → Finalizada)

Relaciones: Se conecta con tipo de obra, responsable, gastos y obreros

3. TABLAS DE GESTIÓN DE OBREROS
   obreros
   Propósito: Catálogo maestro de todos los empleados/obreros

Información clave: Datos personales, laborales y contractuales

Control:

tipo_contrato (PERMANENTE, TEMPORAL, POR_OBRA)

fecha_ingreso y fecha_salida para historial laboral

Único: Cédula y código de obrero como identificadores únicos

asignacion_obreros
Propósito: Relaciona obreros con obras específicas (muchos a muchos)

Funcionalidad: Controla en qué obra trabaja cada obrero y cuándo

Histórico: Permite saber dónde trabajó un obrero en fechas pasadas

Salario específico: El salario puede variar según la obra asignada

pagos_obreros
Propósito: Registra todos los pagos realizados a obreros

Cálculos automáticos:

Horas extras y su monto

Bonificaciones y deducciones

Monto total calculado

Control: Evita pagos duplicados por periodo (índice único obra-obrero-periodo)

Trazabilidad: Quién registró el pago, método de pago, referencia

4. TABLAS DE CONTROL DE GASTOS
   categorias_gasto
   Propósito: Clasifica los diferentes tipos de gastos

Funcionalidad:

tipo_gasto: MATERIAL, EQUIPO, SERVICIO, OTRO

requiere_aprobacion: Gastos que necesitan autorización previa

Organización: Permite reportes por categoría y tipo

gastos
Propósito: Registro detallado de todos los gastos por obra

Flujo de aprobación:

Pendiente → Aprobado/Rechazado → Pagado

Control de quién registra vs quién aprueba (separación de funciones)

Documentación: Número de factura, proveedor, descripción

Control financiero: Cada gasto se asocia a una obra específica

5. TABLAS DE CONTROL DE ATENCIONES Y CUPOS
   control_cupos_mensual
   Propósito: Lleva el conteo de cupos utilizados por área y mes

Funcionalidad:

Actualización automática vía triggers

Control preventivo de sobrecupos

Cálculo: Cupos disponibles = cupo_maximo - cupos_utilizados

atenciones
Propósito: Registra las atenciones a asegurados (para las vistas solicitadas)

Información del asegurado: Número de ficha, nombre, cédula

Seguimiento:

Fecha y hora de asignación

Estado de la atención

Área y usuario que atiende

Control: Se actualizan los cupos automáticamente al insertar o cancelar atenciones

RELACIONES CLAVE ENTRE TABLAS
text
OBRAS ─┬── tiene ── GASTOS
├── tiene ── PAGOS_OBREROS (a través de asignación)
└── tiene ── ASIGNACION_OBREROS ─── tiene ── OBREROS

AREAS ─┬── controla ── CONTROL_CUPOS_MENSUAL
└── recibe ── ATENCIONES ─── atiende ── USUARIOS

USUARIOS ─── pertenecen a ─── ROLES
FLUJOS DE TRABAJO PRINCIPALES
Flujo de Obra
Se crea un TIPO_OBRA (categoría)

Se registra una OBRA con su presupuesto

Se asignan OBREROS a la obra

Se registran GASTOS de materiales/servicios

Se realizan PAGOS periódicos a obreros

Se monitorea el costo actual vs presupuesto

Flujo de Atenciones
Un asegurado llega con su ficha

Se verifica CUPOS_DISPONIBLES en el área

Se registra la ATENCION

El trigger actualiza CONTROL_CUPOS_MENSUAL

Se puede consultar el histórico vía VISTA_HISTORICO_ATENCIONES

Flujo de Seguridad
Usuario se autentica contra USUARIOS

El sistema verifica ROL y permisos

Cada acción importante se registra en AUDITORIA_ACCIONES

Intentos fallidos pueden bloquear al usuario
