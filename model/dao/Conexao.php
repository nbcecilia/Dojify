
<?php
// model/dao/conexao.php
class Conexao {
    private static ?PDO $instancia = null;

    public static function getConexao(): PDO {
        if (self::$instancia === null) {
            $host = 'localhost';
            $db   = 'dojify_1';
            $user = 'root';
            $pass = '';

            try {
                self::$instancia = new PDO("mysql:host={$host};dbname={$db};charset=utf8mb4", $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                die("Erro na conexão: " . $e->getMessage());
            }
        }
        return self::$instancia;
    }
}

