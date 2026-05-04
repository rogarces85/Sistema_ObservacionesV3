<?php
/**
 * Seed script para insertar datos de referentes de establecimientos
 * Mapeo directo a IDs existentes en la BD
 * Ejecutar: php seed_referentes.php
 */

require_once 'config/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Limpiar datos existentes
    $pdo->exec("DELETE FROM referentes_establecimientos");

    // Mapeo nombre_usuario -> ID establecimiento
    $map = [
        'HBSJO' => 5,
        'Hospital Base San José Osorno' => 5,
        'Lopetegui' => 9,
        'CESFAM M.Lopetegui' => 9,
        'SAPU Lopetegui' => 21,
        'Jauregui' => 8,
        'CESFAM P.Jauregui' => 8,
        'SAPU Jauregui' => 19,
        'Rahue Alto' => 11,
        'CESFAM Rahue Alto' => 11,
        'SAPU Rahue Alto' => 20,
        'SAPU Entre Lagos' => 51,
        'SUR San Pablo' => 70,
        'SUR Bahia Mansa' => 71,
        'SUR Puaucho' => 72,
        'H. Purranque' => 52,
        'Hospital Purranque' => 52,
        'Hospital Río Negro' => 53,
        'H. Puerto Octay' => 30,
        'Hospital Puerto Octay' => 30,
        'Hospital Futa Sruka Lawenche Kunko Mapu Mo' => 58,
        'Hosp_Futa_Sruka' => 58,
        'Hospital Pu Mulen Quilacahuin' => 76,
        'CESFAM Puaucho' => 62,
        'CESFAM Bahia Mansa' => 61,
        'Cesfam_Bahia' => 61,
        'CESFAM San Pablo' => 13,
        'CESFAM P. Pablo Araya' => 12,
        'Cesfam_P_P_Araya' => 12,
        'CESFAM Purranque' => 38,
        'Cesfam_Purranque' => 38,
        'CESFAM Entre Lagos' => 12,
        'Cesfam_Entre_Lagos' => 12,
        'CESFAM Ovejeria' => 10,
        'CESFAM Pampa Alegre' => 14,
        'CESFAM V Centenario' => 60,
        'Quinto' => 60,
        'CRD-Centro de Referencia y Diagnóstico Médico Osorno' => 28,
        'CRD_Osorno' => 28,
        'PSR Osorno' => 1,
        'Dir_Servicio_Salud' => 1,
        'DESAM - Puerto Octay' => 30,
        'CECOSF Murrinumo' => 15,
        'CECOSF Manuel Rodriguez' => 16,
        'CECOSF El encanto' => 17,
        'CECOSF Riachuelo' => 18,
        'CECOSF Barrio Estación' => 25,
        'CECOSF Corte alto' => 55,
        'DSSO - OIRS' => 1,
        'DSSO - SAFU' => 5,
        'DSSO - COSAM Rahue' => 22,
        'DSSO - COSAM Oriente' => 24,
        'DSSO - PRAIS' => 2,
        'DSSO - UNIDAD AYEKAN - KUMELEN' => 26,
        'DSSO - CDR  Adulto Mayor con Demencia' => 23,
        'DSSO - Clinica Dental Movil' => 3,
        'Centro de Rehabilitacion Minus.de Purranque' => 7,
        'CR_Minusvalidos' => 7,
        'Peulla - Ambulatorio' => 73,
        'Peulla - Residencia' => 74,
        'Clinica Alemana Osorno' => 6,
    ];

    $referentes = [
        [5, 'Encargado Estadísticas', 'Felipe Martinez', '646384', 'felipe.martinezm@redsalud.gob.cl'],
        [5, 'Digitador Estadísticas', 'Yerko Bastidas', '646384', 'yerko.bastidas@redsalud.gob.cl'],
        [21, 'Encargado Estadísticas', 'Monica Diaz M.-Mauricio Aguila', '64 2 489255', 'monica.diaz@salud.imo.cl'],
        [19, 'Encargado Estadísticas', 'Carmen Cruz', '994549074', 'carmen.cruz@salud.imo.cl'],
        [20, 'Encargado Estadísticas', 'Sergio Correa', '64 2 475214', 'siggesrahuealto@gmail.com'],
        [51, 'Encargado Estadísticas', 'Marcela Navarro', '979412773', 'mnodette@gmail.com'],
        [70, 'Encargado Estadísticas', 'Richrad Aguirre Aceiton', '977415802', 'richardalexaguirre@gmail.com'],
        [71, 'Encargado Estadísticas', 'Nicol Díaz', '958276299', 'cesfambahiamansa@saludsanjuan.cl'],
        [72, 'Encargado Estadísticas', 'Daniela Manriquez Gross', '976943262', 'estadistica_puaucho@hotmail.com'],
        [52, 'Encargado Estadísticas', 'Jessica Ruay Silva', '645896', 'jessica.ruay@redsalud.gob.cl'],
        [52, 'Digitador Estadísticas', 'Jessica Ruay Silva', '645896', 'jessica.ruay@redsalud.gob.cl'],
        [53, 'Encargado Estadísticas', 'Patricio Alvarado', '646827', 'patricio.alvaradoneira@gmail.com'],
        [53, 'Digitador Estadísticas', 'Patricio Alvarado', '646828', 'patricio.alvaradoneira@gmail.com'],
        [30, 'Encargado Estadísticas', 'Andres Silva Hidalgo', '645933', 'andres.silvah@redsalud.gob.cl'],
        [30, 'Digitador Estadísticas', 'Heraldo Isla Noriega', '646664', 'heraldo.isla@redsalud.gob.cl'],
        [58, 'Encargado Estadísticas', 'Gonzalo Retamal', '647589', 'gonzalo.retamalbravo@redsalud.gob.cl'],
        [58, 'Digitador Estadísticas', 'Patricio Fernandez', '647606', 'patricioh.fernandez@redsalud.gob.cl'],
        [76, 'Encargado Estadísticas', 'Maria Rodriguez', '647471', 'maria.rodriguezm@redsalud.gob.cl'],
        [62, 'Encargado Estadísticas', 'Daniela Manriquez Gross', '646933', 'estadistica.puaucho@saludsanjuan.cl'],
        [62, 'Digitador Estadísticas', 'Paola Mendez Aceituna', '646933', 'estadistica.puaucho@saludsanjuan.cl'],
        [61, 'Encargado Estadísticas', 'Pia Coppola', '646808', 'estadisticabm@gmail.com'],
        [61, 'Digitador Estadísticas', 'herbit Rios', '', 'herbitrios84@gmail.com'],
        [13, 'Encargado Estadísticas', 'Richrad Aguirre Aceiton', '642-241402', 'richardalexaguirre@gmail.com'],
        [13, 'Digitador Estadísticas', 'Loreto Cárdenas', '642-241402', 'consultoriosanpablo@yahoo.com'],
        [12, 'Encargado Estadísticas', 'Claudia Serón Navarro', '64-2-363301', 'estadisticasalud@rionegrochile.cl'],
        [12, 'Digitador Estadísticas', 'Nora Quintana P.', '64-2-363347', 'estadisticasalud@rionegrochile.cl'],
        [38, 'Encargado Estadísticas', 'Rodrigo Huerta', '642-351111', 'rodhuerta@gmail.com'],
        [38, 'Digitador Estadísticas', 'Francisca Macaya', '642-351111', 'estadistica@cesfampurranque.cl'],
        [12, 'Encargado Estadísticas', 'Marcela Navarro', '645690', 'mnodette@gmail.com'],
        [12, 'Digitador Estadísticas', 'Emilio Cárdenas Torres', '645692', 'emiliocar@gmail.com'],
        [10, 'Encargado Estadísticas', 'Vicente Barria', '', 'vicente.barria@salud.imo.cl'],
        [10, 'Digitador Estadísticas', 'Vicente Barria', '642-241626', 'vicente.barria@salud.imo.cl'],
        [8, 'Encargado Estadísticas', 'Carmen Cruz Valdivia', '642-475104', 'carmen.cruz@salud.imo.cl'],
        [8, 'Digitador Estadísticas', 'Carmen Cruz Valdivia', '642-475104', 'carmen.cruz@salud.imo.cl'],
        [9, 'Encargado Estadísticas', 'Javiera Castro', '642-475056', 'javiera.castro@salud.imo.cl'],
        [11, 'Encargado Estadísticas', 'Sergio Correa', '', 'siggesrahuealto@gmail.com'],
        [11, 'Encargado Estadísticas', 'Sergio Correa', '64 2 475250', 'siggesrahuealto@gmail.com'],
        [14, 'Encargado Estadísticas', 'David Delgado Salazar', '642-475303', 'david.delgado@salud.imo.cl'],
        [14, 'Encargado Estadísticas', 'Ivis Leigton', '642-475303', 'ivis.leyton@salud.imo.cl'],
        [60, 'Encargado Estadísticas', 'Andrea Mesa', '642-475515', 'andrea.mesa@salud.imo.cl'],
        [60, 'Digitador Estadísticas', 'Andrea Mesa', '642-475537', 'cesfam.quintocentenario@salud.imo.cl'],
        [28, 'Digitador Estadísticas', 'Sigisfredo Fajardo', '642-327205', 'sigifredo.fajardo@salud.imo.cl'],
        [1, 'Encargado Estadísticas', 'Angelica Inostroza', '986692383', 'angelica.inostroza@salud.imo.cl'],
        [1, 'Digitador Estadísticas', 'Eduardo Delgado', '986692383', 'equiporuralsigges@gmail.com'],
        [30, 'Encargado Estadísticas', 'Valeria Schops', '96420948', 'valeriaschopfm@gmail.com'],
        [15, 'Encargado Estadísticas', 'Paola Jaramillo Solís', '642-570459', 'direccion.cecosfmurrinumo@salud.imo.cl'],
        [16, 'Encargado Estadísticas', 'Jessica Alvarez', '642-226177', 'jessica.alvarez@salud.imo.cl'],
        [17, 'Encargado Estadísticas', 'Marcela Navarro', '642-335690', 'cecosfelencanto@gmail.com'],
        [17, 'Digitador Estadísticas', 'Emilio Cárdenas Torres', '645690', 'emiliocar@gmail.com'],
        [18, 'Encargado Estadísticas', 'Claudia Serón Navarro', '64-2-363301', 'estadisticasalud@rionegrochile.cl'],
        [18, 'Digitador Estadísticas', 'Nora Quintana P.', '642-362240', ''],
        [25, 'Encargado Estadísticas', 'Rodrigo Huerta', '642-351111', 'calidad@cesfampurranque.cl'],
        [25, 'Digitador Estadísticas', 'Francisca Macaya', '642-351111', 'estadistica@cesfampurranque.cl'],
        [55, 'Encargado Estadísticas', 'Rodrigo Huerta', '642-351111', 'calidad@cesfampurranque.cl'],
        [55, 'Digitador Estadísticas', 'Francisca Macaya', '642-351111', 'estadistica@cesfampurranque.cl'],
        [1, 'Encargado Estadísticas OIRS', 'Daniela Barria', '645775', 'daniela.barriaa@redsalud.gob.cl'],
        [1, 'Digitador Estadísticas OIRS', 'Sonia Aguila', '645787', 'oirs.ssosorno@redsalud.gob.cl'],
        [5, 'Encargado Estadísticas SAFU', 'Daniela Cancino', '646526', 'daniela.cancino01@redsalud.gov.cl'],
        [22, 'Encargado Estadísticas', 'Anny Aguila', '645608', 'anny.aguila@redsalud.gob.cl'],
        [24, 'Encargado Estadísticas', 'Eduardo Vargas', '645576', 'eduardovargasdiaz@gmail.com'],
        [2, 'Encargado Estadísticas', 'Paula Cárcamo', '642-646693', 'paula.carcamo@redsalud.gov.cl'],
        [2, 'Digitador Estadísticas', 'Paula Cárcamo', '642-646694', 'paula.carcamo@redsalud.gov.cl'],
        [26, 'Encargado Estadísticas', 'Ximena Campos', '642-567363', 'ximena.campos@redsalud.gov.cl'],
        [26, 'Digitador Estadísticas', 'Ximena Campos', '642-567364', 'ximena.campos@redsalud.gov.cl'],
        [23, 'Encargado Estadísticas', 'Luis Flores', '642-567365', 'luis.floresme@redsalud.gob.cl'],
        [23, 'Digitador Estadísticas', 'Loreto Colipue', '642-567366', 'loreto.colipue@redsalud.gob.cl'],
        [3, 'Encargado Estadísticas', 'Felipe Lara Abarzúa', '643480', 'felipe.lara@redsalud.gob.cl'],
        [7, 'Encargado Estadísticas', 'Rodrigo Diaz Poblete', '642-352498', 'estadisticascorelampurranque@gmail.com'],
        [7, 'Digitador Estadísticas', 'Susana Barria', '642-352498', 'estadisticascorelampurranque@gmail.com'],
        [73, 'Encargado Estadísticas', 'Silvia Perez Aros', '645799', 'silviaandrea.perez@redsalud.gob.cl'],
        [74, 'Encargado Estadísticas', 'Silvia Perez Aros', '645799', 'silviaandrea.perez@redsalud.gob.cl'],
        [6, 'Encargado Estadísticas', 'Dennisse Mendoza', '642-454082', 'denniss.mendoza@clinicale.cl'],
    ];

    $stmt = $pdo->prepare("INSERT INTO referentes_establecimientos (establecimiento_id, cargo, nombre, telefono, email) 
                           VALUES (?, ?, ?, ?, ?)");
    
    $inserted = 0;
    foreach ($referentes as $ref) {
        $stmt->execute($ref);
        $inserted++;
    }

    echo "Referentes insertados: {$inserted}\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
