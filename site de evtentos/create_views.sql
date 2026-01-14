-- ========================================
-- VIEWS PARA O SISTEMA DE GESTÃO DE EVENTOS
-- ========================================
-- Execute este script no phpMyAdmin para criar views úteis
-- que facilitam consultas e relatórios

USE event_management;

-- ========================================
-- VIEW 1: Detalhes Completos dos Eventos
-- ========================================
-- Mostra todos os eventos com informações calculadas
DROP VIEW IF EXISTS event_details;
CREATE VIEW event_details AS
SELECT 
    e.id,
    e.title,
    e.description,
    e.event_date,
    e.event_time,
    e.location,
    e.ticket_price,
    e.available_tickets,
    e.image,
    e.status,
    e.created_at,
    u.name AS created_by_name,
    -- Calcular bilhetes vendidos
    COALESCE(SUM(p.quantity), 0) AS tickets_sold,
    -- Calcular receita total
    COALESCE(SUM(p.total_price), 0) AS total_revenue,
    -- Calcular percentagem de ocupação
    ROUND((COALESCE(SUM(p.quantity), 0) / (e.available_tickets + COALESCE(SUM(p.quantity), 0))) * 100, 2) AS occupancy_percentage,
    -- Verificar se o evento está esgotado
    CASE 
        WHEN e.available_tickets = 0 THEN 'Esgotado'
        WHEN e.available_tickets < 50 THEN 'Últimos Bilhetes'
        ELSE 'Disponível'
    END AS availability_status,
    -- Dias até o evento
    DATEDIFF(e.event_date, CURDATE()) AS days_until_event,
    -- Status temporal do evento
    CASE 
        WHEN e.event_date < CURDATE() THEN 'Passado'
        WHEN e.event_date = CURDATE() THEN 'Hoje'
        WHEN DATEDIFF(e.event_date, CURDATE()) <= 7 THEN 'Esta Semana'
        WHEN DATEDIFF(e.event_date, CURDATE()) <= 30 THEN 'Este Mês'
        ELSE 'Futuro'
    END AS time_status
FROM events e
LEFT JOIN users u ON e.created_by = u.id
LEFT JOIN purchases p ON e.id = p.event_id AND p.status = 'confirmed'
GROUP BY e.id, e.title, e.description, e.event_date, e.event_time, 
         e.location, e.ticket_price, e.available_tickets, e.image, 
         e.status, e.created_at, u.name;

-- ========================================
-- VIEW 2: Resumo de Vendas por Evento
-- ========================================
-- Estatísticas de vendas para cada evento
DROP VIEW IF EXISTS event_sales_summary;
CREATE VIEW event_sales_summary AS
SELECT 
    e.id AS event_id,
    e.title AS event_name,
    e.event_date,
    e.location,
    e.ticket_price,
    COUNT(DISTINCT p.id) AS total_purchases,
    COUNT(DISTINCT p.user_id) AS unique_customers,
    COALESCE(SUM(p.quantity), 0) AS total_tickets_sold,
    COALESCE(SUM(p.total_price), 0) AS total_revenue,
    COALESCE(AVG(p.quantity), 0) AS avg_tickets_per_purchase,
    MIN(p.purchase_date) AS first_sale_date,
    MAX(p.purchase_date) AS last_sale_date,
    e.available_tickets AS tickets_remaining
FROM events e
LEFT JOIN purchases p ON e.id = p.event_id AND p.status = 'confirmed'
GROUP BY e.id, e.title, e.event_date, e.location, e.ticket_price, e.available_tickets
ORDER BY total_revenue DESC;

-- ========================================
-- VIEW 3: Perfil de Clientes
-- ========================================
-- Informações sobre compras de cada cliente
DROP VIEW IF EXISTS customer_sales_summary;
CREATE VIEW customer_sales_summary AS
SELECT 
    u.id AS customer_id,
    u.name AS customer_name,
    u.email,
    u.phone,
    u.created_at AS registration_date,
    COUNT(DISTINCT p.id) AS total_purchases,
    COUNT(DISTINCT p.event_id) AS events_attended,
    COALESCE(SUM(p.quantity), 0) AS total_tickets_purchased,
    COALESCE(SUM(p.total_price), 0) AS total_spent,
    COALESCE(AVG(p.total_price), 0) AS avg_purchase_value,
    MIN(p.purchase_date) AS first_purchase_date,
    MAX(p.purchase_date) AS last_purchase_date,
    -- Classificação do cliente
    CASE 
        WHEN COALESCE(SUM(p.total_price), 0) >= 500 THEN 'VIP'
        WHEN COALESCE(SUM(p.total_price), 0) >= 200 THEN 'Premium'
        WHEN COALESCE(SUM(p.total_price), 0) >= 50 THEN 'Regular'
        WHEN COALESCE(SUM(p.total_price), 0) > 0 THEN 'Novo'
        ELSE 'Sem Compras'
    END AS customer_tier
FROM users u
LEFT JOIN purchases p ON u.id = p.user_id AND p.status = 'confirmed'
WHERE u.role = 'user'
GROUP BY u.id, u.name, u.email, u.phone, u.created_at
ORDER BY total_spent DESC;

-- ========================================
-- VIEW 4: Status dos Bilhetes
-- ========================================
-- Visão geral do status de todos os bilhetes
DROP VIEW IF EXISTS tickets_status;
CREATE VIEW tickets_status AS
SELECT 
    e.id AS event_id,
    e.title AS event_name,
    e.event_date,
    e.status AS event_status,
    -- Capacidade inicial (calculada)
    (e.available_tickets + COALESCE(SUM(p.quantity), 0)) AS initial_capacity,
    -- Bilhetes vendidos
    COALESCE(SUM(CASE WHEN p.status = 'confirmed' THEN p.quantity ELSE 0 END), 0) AS tickets_sold,
    -- Bilhetes pendentes
    COALESCE(SUM(CASE WHEN p.status = 'pending' THEN p.quantity ELSE 0 END), 0) AS tickets_pending,
    -- Bilhetes cancelados
    COALESCE(SUM(CASE WHEN p.status = 'cancelled' THEN p.quantity ELSE 0 END), 0) AS tickets_cancelled,
    -- Bilhetes disponíveis
    e.available_tickets AS tickets_available,
    -- Percentagem vendida
    ROUND((COALESCE(SUM(CASE WHEN p.status = 'confirmed' THEN p.quantity ELSE 0 END), 0) / 
           (e.available_tickets + COALESCE(SUM(p.quantity), 0))) * 100, 2) AS percentage_sold,
    -- Status de disponibilidade
    CASE 
        WHEN e.available_tickets = 0 THEN 'ESGOTADO'
        WHEN e.available_tickets < 10 THEN 'CRÍTICO'
        WHEN e.available_tickets < 50 THEN 'BAIXO'
        ELSE 'DISPONÍVEL'
    END AS stock_status
FROM events e
LEFT JOIN purchases p ON e.id = p.event_id
GROUP BY e.id, e.title, e.event_date, e.status, e.available_tickets
ORDER BY e.event_date;

-- ========================================
-- VIEW 5: Eventos Populares
-- ========================================
-- Ranking dos eventos mais vendidos
DROP VIEW IF EXISTS popular_events;
CREATE VIEW popular_events AS
SELECT 
    e.id,
    e.title,
    e.event_date,
    e.location,
    e.ticket_price,
    COALESCE(SUM(p.quantity), 0) AS tickets_sold,
    COALESCE(SUM(p.total_price), 0) AS revenue,
    COUNT(DISTINCT p.user_id) AS unique_buyers,
    -- Ranking
    RANK() OVER (ORDER BY COALESCE(SUM(p.quantity), 0) DESC) AS popularity_rank
FROM events e
LEFT JOIN purchases p ON e.id = p.event_id AND p.status = 'confirmed'
WHERE e.status = 'active'
GROUP BY e.id, e.title, e.event_date, e.location, e.ticket_price
HAVING tickets_sold > 0
ORDER BY tickets_sold DESC;

-- ========================================
-- VIEW 6: Receita Mensal
-- ========================================
-- Análise de receita por mês
DROP VIEW IF EXISTS monthly_revenue;
CREATE VIEW monthly_revenue AS
SELECT 
    YEAR(p.purchase_date) AS year,
    MONTH(p.purchase_date) AS month,
    DATE_FORMAT(p.purchase_date, '%Y-%m') AS year_month,
    DATE_FORMAT(p.purchase_date, '%M %Y') AS month_name,
    COUNT(DISTINCT p.id) AS total_purchases,
    COUNT(DISTINCT p.user_id) AS unique_customers,
    COUNT(DISTINCT p.event_id) AS events_sold,
    SUM(p.quantity) AS total_tickets,
    SUM(p.total_price) AS total_revenue,
    AVG(p.total_price) AS avg_purchase_value
FROM purchases p
WHERE p.status = 'confirmed'
GROUP BY YEAR(p.purchase_date), MONTH(p.purchase_date), 
         DATE_FORMAT(p.purchase_date, '%Y-%m'),
         DATE_FORMAT(p.purchase_date, '%M %Y')
ORDER BY year DESC, month DESC;

-- ========================================
-- VIEW 7: Eventos Próximos
-- ========================================
-- Lista de eventos que acontecerão em breve
DROP VIEW IF EXISTS upcoming_events;
CREATE VIEW upcoming_events AS
SELECT 
    e.id,
    e.title,
    e.description,
    e.event_date,
    e.event_time,
    e.location,
    e.ticket_price,
    e.available_tickets,
    e.image,
    DATEDIFF(e.event_date, CURDATE()) AS days_until,
    CASE 
        WHEN DATEDIFF(e.event_date, CURDATE()) = 0 THEN 'HOJE'
        WHEN DATEDIFF(e.event_date, CURDATE()) = 1 THEN 'AMANHÃ'
        WHEN DATEDIFF(e.event_date, CURDATE()) <= 7 THEN 'ESTA SEMANA'
        WHEN DATEDIFF(e.event_date, CURDATE()) <= 30 THEN 'ESTE MÊS'
        ELSE 'PRÓXIMO'
    END AS urgency_label,
    COALESCE(SUM(p.quantity), 0) AS tickets_sold
FROM events e
LEFT JOIN purchases p ON e.id = p.event_id AND p.status = 'confirmed'
WHERE e.status = 'active' 
  AND e.event_date >= CURDATE()
GROUP BY e.id, e.title, e.description, e.event_date, e.event_time, 
         e.location, e.ticket_price, e.available_tickets, e.image
ORDER BY e.event_date ASC, e.event_time ASC
LIMIT 10;

-- ========================================
-- VIEW 8: Dashboard Administrativo
-- ========================================
-- Métricas principais para o painel admin
DROP VIEW IF EXISTS admin_dashboard;
CREATE VIEW admin_dashboard AS
SELECT 
    -- Totais gerais
    (SELECT COUNT(*) FROM events WHERE status = 'active') AS total_active_events,
    (SELECT COUNT(*) FROM events WHERE status = 'active' AND event_date >= CURDATE()) AS upcoming_events_count,
    (SELECT COUNT(*) FROM users WHERE role = 'user') AS total_customers,
    (SELECT COUNT(*) FROM purchases WHERE status = 'confirmed') AS total_purchases,
    
    -- Receitas
    (SELECT COALESCE(SUM(total_price), 0) FROM purchases WHERE status = 'confirmed') AS total_revenue,
    (SELECT COALESCE(SUM(total_price), 0) FROM purchases 
     WHERE status = 'confirmed' AND MONTH(purchase_date) = MONTH(CURDATE())) AS monthly_revenue,
    (SELECT COALESCE(SUM(total_price), 0) FROM purchases 
     WHERE status = 'confirmed' AND DATE(purchase_date) = CURDATE()) AS daily_revenue,
    
    -- Bilhetes
    (SELECT COALESCE(SUM(quantity), 0) FROM purchases WHERE status = 'confirmed') AS total_tickets_sold,
    (SELECT COALESCE(SUM(available_tickets), 0) FROM events WHERE status = 'active') AS total_tickets_available,
    
    -- Médias
    (SELECT COALESCE(AVG(total_price), 0) FROM purchases WHERE status = 'confirmed') AS avg_purchase_value,
    (SELECT COALESCE(AVG(quantity), 0) FROM purchases WHERE status = 'confirmed') AS avg_tickets_per_purchase,
    
    -- Eventos populares
    (SELECT title FROM events e 
     LEFT JOIN purchases p ON e.id = p.event_id 
     WHERE p.status = 'confirmed'
     GROUP BY e.id, e.title 
     ORDER BY SUM(p.quantity) DESC 
     LIMIT 1) AS most_popular_event,
    
    -- Cliente top
    (SELECT u.name FROM users u 
     LEFT JOIN purchases p ON u.id = p.user_id 
     WHERE p.status = 'confirmed'
     GROUP BY u.id, u.name 
     ORDER BY SUM(p.total_price) DESC 
     LIMIT 1) AS top_customer;

-- ========================================
-- VERIFICAÇÃO DAS VIEWS CRIADAS
-- ========================================

-- Listar todas as views criadas
SELECT TABLE_NAME, TABLE_ROWS 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'event_management' 
  AND TABLE_TYPE = 'VIEW'
ORDER BY TABLE_NAME;

-- Testar cada view (comentar/descomentar conforme necessário)
-- SELECT * FROM event_details LIMIT 5;
-- SELECT * FROM event_sales_summary LIMIT 5;
-- SELECT * FROM customer_sales_summary LIMIT 5;
-- SELECT * FROM tickets_status LIMIT 5;
-- SELECT * FROM popular_events LIMIT 5;
-- SELECT * FROM monthly_revenue LIMIT 5;
-- SELECT * FROM upcoming_events LIMIT 5;
-- SELECT * FROM admin_dashboard;
