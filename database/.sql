-- ======================================================
-- SCRIPT PARA MARIADB / MYSQL
-- MARKETPLACE CON MÚLTIPLES VENDEDORES
-- ======================================================

-- ======================================================
-- 1. TABLA USUARIOS
-- ======================================================
CREATE TABLE usuarios (
    id_usuario CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    tipo ENUM('comprador', 'vendedor', 'admin') NOT NULL DEFAULT 'comprador',
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    direccion TEXT,
    telefono VARCHAR(20),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- 2. TABLA VENDEDORES (extiende usuarios)
-- ======================================================
CREATE TABLE vendedores (
    id_vendedor CHAR(36) PRIMARY KEY,
    razon_social VARCHAR(200) NOT NULL,
    rfc VARCHAR(13) UNIQUE,
    descripcion TEXT,
    calificacion_promedio DECIMAL(2,1) DEFAULT 0.0,
    politicas_devolucion TEXT,
    banco_cuenta VARCHAR(100),
    FOREIGN KEY (id_vendedor) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_rfc (rfc),
    INDEX idx_calificacion (calificacion_promedio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- 3. TABLA PRODUCTOS
-- ======================================================
CREATE TABLE productos (
    id_producto CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    id_vendedor CHAR(36) NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL CHECK (precio >= 0),
    stock INT NOT NULL DEFAULT 0 CHECK (stock >= 0),
    categoria VARCHAR(100),
    estado ENUM('activo', 'agotado', 'oculto') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_vendedor) REFERENCES vendedores(id_vendedor) ON DELETE CASCADE,
    INDEX idx_vendedor (id_vendedor),
    INDEX idx_categoria (categoria),
    INDEX idx_estado (estado),
    INDEX idx_precio (precio),
    FULLTEXT idx_nombre_desc (nombre, descripcion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- 4. TABLA CARRITO (para usuarios autenticados y anónimos)
-- ======================================================
CREATE TABLE carrito (
    id_carrito CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    id_usuario CHAR(36) NULL,
    session_token VARCHAR(255) NULL,
    id_producto CHAR(36) NOT NULL,
    cantidad INT NOT NULL CHECK (cantidad > 0),
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE,
    INDEX idx_usuario (id_usuario),
    INDEX idx_session (session_token),
    INDEX idx_fecha (fecha_agregado),
    CONSTRAINT carrito_check_usuario_o_session CHECK (
        (id_usuario IS NOT NULL AND session_token IS NULL) OR
        (id_usuario IS NULL AND session_token IS NOT NULL)
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- 5. TABLA ORDENES
-- ======================================================
CREATE TABLE ordenes (
    id_orden CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    id_comprador CHAR(36) NOT NULL,
    fecha_orden TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'pagado', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',
    total DECIMAL(12,2) NOT NULL DEFAULT 0.0 CHECK (total >= 0),
    metodo_pago VARCHAR(50),
    direccion_envio TEXT NOT NULL,
    id_transaccion_pago CHAR(36) NULL,
    FOREIGN KEY (id_comprador) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT,
    INDEX idx_comprador (id_comprador),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- 6. TABLA DETALLE_ORDEN
-- ======================================================
CREATE TABLE detalle_orden (
    id_detalle CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    id_orden CHAR(36) NOT NULL,
    id_producto CHAR(36) NOT NULL,
    cantidad INT NOT NULL CHECK (cantidad > 0),
    precio_unitario DECIMAL(10,2) NOT NULL CHECK (precio_unitario >= 0),
    id_vendedor CHAR(36) NOT NULL,  -- denormalizado para splits
    FOREIGN KEY (id_orden) REFERENCES ordenes(id_orden) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE RESTRICT,
    FOREIGN KEY (id_vendedor) REFERENCES vendedores(id_vendedor) ON DELETE RESTRICT,
    INDEX idx_orden (id_orden),
    INDEX idx_producto (id_producto),
    INDEX idx_vendedor (id_vendedor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- 7. TABLA PAGOS
-- ======================================================
CREATE TABLE pagos (
    id_pago CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    id_orden CHAR(36) NOT NULL,
    monto DECIMAL(12,2) NOT NULL CHECK (monto > 0),
    metodo VARCHAR(50) NOT NULL,
    referencia_externa VARCHAR(255),
    estado ENUM('autorizado', 'capturado', 'reembolsado') DEFAULT 'autorizado',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_orden) REFERENCES ordenes(id_orden) ON DELETE RESTRICT,
    UNIQUE INDEX idx_orden_pago (id_orden),
    INDEX idx_referencia (referencia_externa),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Actualizar la referencia en ordenes después de crear pagos
ALTER TABLE ordenes 
    ADD CONSTRAINT fk_orden_pago 
    FOREIGN KEY (id_transaccion_pago) REFERENCES pagos(id_pago) ON DELETE SET NULL;

-- ======================================================
-- 8. TABLA SPLIT_PAGOS (distribución entre vendedores y comisión)
-- ======================================================
CREATE TABLE split_pagos (
    id_split CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    id_pago CHAR(36) NOT NULL,
    id_vendedor CHAR(36) NOT NULL,
    monto_vendedor DECIMAL(12,2) NOT NULL CHECK (monto_vendedor >= 0),
    monto_comision DECIMAL(12,2) NOT NULL CHECK (monto_comision >= 0),
    estado_liberacion ENUM('pendiente', 'liberado') DEFAULT 'pendiente',
    FOREIGN KEY (id_pago) REFERENCES pagos(id_pago) ON DELETE CASCADE,
    FOREIGN KEY (id_vendedor) REFERENCES vendedores(id_vendedor) ON DELETE RESTRICT,
    INDEX idx_pago (id_pago),
    INDEX idx_vendedor (id_vendedor),
    INDEX idx_estado_liberacion (estado_liberacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- 9. TABLA ENVIOS
-- ======================================================
CREATE TABLE envios (
    id_envio CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    id_orden CHAR(36) NOT NULL,
    transportadora VARCHAR(100),
    numero_guia VARCHAR(100),
    fecha_envio TIMESTAMP NULL,
    fecha_entrega TIMESTAMP NULL,
    estado_envio VARCHAR(50) DEFAULT 'pendiente',
    FOREIGN KEY (id_orden) REFERENCES ordenes(id_orden) ON DELETE RESTRICT,
    UNIQUE INDEX idx_orden_envio (id_orden),
    INDEX idx_guia (numero_guia),
    INDEX idx_estado (estado_envio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- 10. TABLA RESENAS
-- ======================================================
CREATE TABLE resenas (
    id_review CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    id_producto CHAR(36) NOT NULL,
    id_comprador CHAR(36) NOT NULL,
    calificacion INT NOT NULL CHECK (calificacion BETWEEN 1 AND 5),
    comentario TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    respuesta_vendedor TEXT,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE,
    FOREIGN KEY (id_comprador) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT,
    UNIQUE INDEX idx_producto_comprador (id_producto, id_comprador), -- un comprador solo una reseña por producto
    INDEX idx_calificacion (calificacion),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- 11. TABLA DE COMISIONES (configuración global o por categoría)
-- ======================================================
CREATE TABLE comisiones (
    id_comision CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    categoria VARCHAR(100) NULL, -- NULL = comisión general
    porcentaje DECIMAL(5,2) NOT NULL CHECK (porcentaje BETWEEN 0 AND 100),
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NULL,
    activo BOOLEAN DEFAULT TRUE,
    INDEX idx_categoria (categoria),
    INDEX idx_fechas (fecha_inicio, fecha_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- 12. TABLA DE HISTORIAL DE PRECIOS (opcional)
-- ======================================================
CREATE TABLE historial_precios (
    id_historial CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    id_producto CHAR(36) NOT NULL,
    precio_anterior DECIMAL(10,2) NOT NULL,
    precio_nuevo DECIMAL(10,2) NOT NULL,
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE,
    INDEX idx_producto (id_producto),
    INDEX idx_fecha (fecha_cambio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- TRIGGER PARA ACTUALIZAR CALIFICACIÓN PROMEDIO DE VENDEDOR
-- ======================================================
DELIMITER //

CREATE TRIGGER actualizar_calificacion_vendedor
AFTER INSERT ON resenas
FOR EACH ROW
BEGIN
    DECLARE v_id_vendedor CHAR(36);
    
    -- Obtener el vendedor del producto reseñado
    SELECT id_vendedor INTO v_id_vendedor
    FROM productos
    WHERE id_producto = NEW.id_producto;
    
    -- Actualizar el promedio
    UPDATE vendedores
    SET calificacion_promedio = (
        SELECT AVG(r.calificacion)
        FROM resenas r
        JOIN productos p ON r.id_producto = p.id_producto
        WHERE p.id_vendedor = v_id_vendedor
    )
    WHERE id_vendedor = v_id_vendedor;
END//

DELIMITER ;

-- ======================================================
-- TRIGGER PARA REGISTRAR HISTORIAL DE PRECIOS
-- ======================================================
DELIMITER //

CREATE TRIGGER registrar_historial_precios
BEFORE UPDATE ON productos
FOR EACH ROW
BEGIN
    IF OLD.precio != NEW.precio THEN
        INSERT INTO historial_precios (id_producto, precio_anterior, precio_nuevo)
        VALUES (OLD.id_producto, OLD.precio, NEW.precio);
    END IF;
END//

DELIMITER ;

-- ======================================================
-- VISTA: Órdenes con detalles de vendedores y productos
-- ======================================================
CREATE VIEW vista_ordenes_completas AS
SELECT 
    o.id_orden,
    o.fecha_orden,
    o.estado,
    o.total,
    u.nombre AS comprador_nombre,
    u.email AS comprador_email,
    p.nombre AS producto_nombre,
    do.cantidad,
    do.precio_unitario,
    v.razon_social AS vendedor,
    env.transportadora,
    env.numero_guia,
    env.estado_envio
FROM ordenes o
JOIN usuarios u ON o.id_comprador = u.id_usuario
JOIN detalle_orden do ON o.id_orden = do.id_orden
JOIN productos p ON do.id_producto = p.id_producto
JOIN vendedores v ON do.id_vendedor = v.id_vendedor
LEFT JOIN envios env ON o.id_orden = env.id_orden;

-- ======================================================
-- VISTA: Reporte de ventas por vendedor
-- ======================================================
CREATE VIEW vista_ventas_vendedor AS
SELECT 
    v.id_vendedor,
    v.razon_social,
    COUNT(DISTINCT o.id_orden) AS total_ordenes,
    SUM(do.cantidad) AS unidades_vendidas,
    SUM(do.cantidad * do.precio_unitario) AS venta_bruta,
    SUM(sp.monto_comision) AS comisiones_totales,
    SUM(sp.monto_vendedor) AS neto_vendedor
FROM vendedores v
JOIN detalle_orden do ON v.id_vendedor = do.id_vendedor
JOIN ordenes o ON do.id_orden = o.id_orden
JOIN split_pagos sp ON do.id_detalle = sp.id_split  -- Nota: ajustar join real
WHERE o.estado IN ('pagado', 'enviado', 'entregado')
GROUP BY v.id_vendedor, v.razon_social;