-- Script para actualizar establecimientos
-- Reemplaza todos los establecimientos con el listado actualizado

USE observaciones_rem;

-- Limpiar tabla de establecimientos (mantener solo si no hay referencias)
-- Si hay observaciones vinculadas, mejor usar UPDATE/INSERT
DELETE FROM establecimientos;

-- Reiniciar el autoincremento
ALTER TABLE establecimientos AUTO_INCREMENT = 1;

-- Insertar establecimientos actualizados
INSERT INTO establecimientos (codigo_establecimiento, nombre, nombre_corto, comuna_id) VALUES
(123011, 'Dirección Servicio Salud Osorno', 'Dir_Serv_Salud', 1),
(123211, 'PRAIS', 'PRAIS', 1),
(123612, 'Clínica Dental Móvil (Osorno)', 'Clinica_dental_Movil', 1),
(123630, 'Departamento de Atención Integral Funcionarios', 'DAIF', 1),
(123130, 'Hospital Base San José de Osorno', 'Hosp_Base', 1),
(123131, 'Hospital de Purranque Dr. Juan Hepp Dubiau', 'Hosp_Purranque', 2),
(123132, 'Hospital de Río Negro', 'Hosp_Rio_Negro', 4),
(123133, 'Hospital de Entre Lagos', 'Hosp_Entre_Lagos', 3),
(123134, 'Hospital Futa Suau Lawrence Kunko Mapu Mo', 'Hosp_Futa_Suau', 6),
(123135, 'Hospital Dr Marure Vyhllmeister', 'Hosp_Salud_Mental_Jolla', 1),
(123201, 'Hospital Misión San Juan de la Costa', 'Hosp_Mision_SJC', 6),
(123202, 'Hospital Médico Socorro de Quilacahuin (San Pablo)', 'Hosp_Socorro_Quilacahuin', 5),
(125202, 'Clínica Alemana Osorno', 'Clin_Alemana_Osor', 1),
(125207, 'Centro de Rehabilitación de Minusválidos', 'CR_Minusvalidos', 1),
(125300, 'Centro de Salud Familiar Dr. Pedro Jáuregui', 'Jauregui', 1),
(125301, 'Centro de Salud Familiar Dr. Marcelo Lopetegui Adams', 'Lopetegui', 1),
(125302, 'Centro de Salud Familiar Ovejería', 'Ovejeria', 1),
(125303, 'Centro de Salud Familiar Rahue Alto', 'Rahue_Alto', 1),
(125304, 'Centro de Salud Familiar Dr. Roberto Jensen', 'Cfam_Entre_Lagos', 3),
(125305, 'Centro de Salud Familiar San Pablo', 'Cesfam_San_Pablo', 5),
(125306, 'Centro de Salud Familiar Pampa Alegre', 'P_Alegre', 1),
(125307, 'Centro de Salud Familiar Purranque', 'Cesfam_Purranque', 2),
(125309, 'Centro de Salud Familiar Pampa Alta Araya Pablo Araya', 'Cesfam_S_P_Araya', 1),
(125310, 'Centro de Salud Familiar Quinta Centenario', 'Quinto', 1),
(125311, 'Centro de Salud Familiar Pilauco', 'Cesfam_Pilauco', 1),
(125312, 'Centro de Salud Familiar Puaucho', 'Cesfam_Puaucho', 6),
(125402, 'Posta de Salud Rural Chacayal', 'PSR_Chacayal', 1),
(125404, 'Posta de Salud Rural Pichi Damas', 'PSR_Pichi_Damas', 1),
(125405, 'Posta de Salud Rural Diguillín', 'PSR_Diguillin', 1),
(125407, 'Posta de Salud Rural Huicha Rupanco', 'PSR_Rupanco', 1),
(125406, 'Posta de Salud Rural Ñad Pilmaiquen', 'PSR_N_P_Ilma', 4),
(125410, 'Posta de Salud Rural Cancura', 'PSR_T_Cancura', 5),
(125411, 'Centro Comunitario de Salud Familiar Corte Alto', 'Cecosf_Corte_Alto', 1),
(125412, 'Posta de Salud Rural Chadmo (Purranque)', 'PSR_Chadmo', 2),
(125413, 'Posta de Salud Rural Coliguai', 'PSR_Coliguai', 2),
(125414, 'Posta de Salud Rural Hueyusca', 'PSR_Hueyusca', 2),
(125415, 'Posta de Salud Rural Concordia', 'PSR_Concordia', 2),
(125416, 'Posta de Salud Rural San Antonio de Purranque', 'PSR_C_Purranq', 2),
(125417, 'Posta de Salud Rural La Naranja', 'PSR_La_Naranja', 2),
(125419, 'Posta de Salud Rural San Pedro de Purranque', 'PSR_S_P_Purranq', 2),
(125420, 'Posta de Salud Rural Collileumo', 'PSR_Collileumo', 2),
(125422, 'Posta de Salud Rural Los Riscos', 'PSR_L_Riscos', 2),
(125423, 'Posta de Salud Rural Ciruelos', 'PSR_Ciruelos', 3),
(125424, 'Posta de Salud Rural Piedras Negras', 'PSR_P_Negras', 3),
(125425, 'Posta de Salud Rural Cancura', 'PSR_Cancura', 3),
(125426, 'Posta de Salud Rural Pallinada', 'PSR_Pallinaca', 3),
(125427, 'Posta de Salud Rural Cuinco', 'PSR_La_Union', 3),
(125429, 'Posta de Salud Rural Coihueco (Puerto Octay)', 'PSR_Coihueco', 7),
(125430, 'Posta de Salud Rural Nothofagus', 'PSR_Nothofagus', 7),
(125431, 'Posta de Salud Rural Ataucai', 'PSR_Ataucapi', 7),
(125432, 'Posta de Salud Rural Gavión', 'PSR_La_Poza', 7),
(125434, 'Posta de Salud Rural Huilma', 'PSR_Huilma', 7),
(125435, 'Posta de Salud Rural Rupanco', 'PSR_Rupanco', 7),
(125436, 'Posta de Salud Rural El Rincón (San Pablo)', 'PSR_El_Rincon', 5),
(125457, 'Posta de Salud Rural Currimahuida', 'PSR_Currimahuida', 5),
(127700, 'Centro Comunitario de Salud Familiar El Encanto', 'CECOSF_Encanto', 1),
(127701, 'Centro Comunitario de Salud Familiar Manuel Rodriguez', 'CECOSF_M_Rodriguez', 1),
(127805, 'Centro Comunitario de Salud Familiar El Encanto', 'CECOSF_Encanto_2', 1),
(127809, 'Centro Comunitario de Salud Familiar Riachulco', 'CECOSF_Riachuelp', 1),
(127800, 'SAPU Centro Osorno', 'SAPU_Centro_Aucal', 1),
(125601, 'SAPU Rahue Alto', 'SAPU_Rahue_Alto', 1),
(200805, 'SAPU Dr. Marcelo Lopetegui Adams', 'SAPU_Lopetegui', 1),
(200209, 'COSAM Rahue', 'Cosam_Rahue', 1),
(200428, 'CDR Cecilia Maiores con Demencia', 'CDPM', 1),
(200434, 'Unidad de Niñez UIO', 'UIO', 1),
(200455, 'Centro Comunitario de Salud Familiar Barrio Estación', 'CECOSF_Barrio_Estacion', 1),
(200477, 'Unidad de Docencia ATEAM', 'UD_Ateam', 1),
(200490, 'Posta de Salud Rural Chamilco', 'PSR_Chamilco', 7),
(200508, 'Centro de Diálisis Diagnóstico Médico Osorno', 'CDR_Osorno_DM', 1),
(200556, 'Hospital Digital', 'Hosp_digital', 1),
(200747, 'SAPU Pampa Alegre', 'SAPU_Pampa_Ale', 1),
(200748, 'SUR San Pablo', 'SUR_San_Pablo', 5),
(200749, 'SUR Puerto Octay', 'SUR_Pto_Octay', 7),
(200750, 'SUR Puaucho', 'SUR_Puaucho', 6),
(201055, 'Terapéutica Familia Ambulatoria', 'Pauilla_Jauregui', 1),
(201199, 'Terapéutica Familia Residencial', 'TFR', 1),
(201485, 'Centro Comunitario de Salud Familiar Las Cascadas', 'CECOSF_Las_Cascadas', 1),
(204857, 'Posta de Salud Rural Anticura', 'PSR_Anticura', 7),
(202043, 'Posta de Salud Rural Pucatrihue', 'PSR_Pucatrihue', 1);

-- Mensaje de finalización
SELECT 'Establecimientos actualizados correctamente' AS mensaje,
       COUNT(*) AS total_establecimientos 
FROM establecimientos;
