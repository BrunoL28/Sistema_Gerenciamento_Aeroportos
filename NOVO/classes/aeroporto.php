<?php
require_once ("estado.php");
require_once ("identificadores.php");
require_once 'coordenada.php';
require_once "persist.php";

class Aeroporto extends persist
{
    private SiglaAeroporto $sigla;
    private string $nome;
    private string $cidade;
    private Estado $estado;
    private static string $local_filename = "aeroporto.txt";

    public function __construct(
        SiglaAeroporto $sigla,
        string $nome,
        string $cidade,
        Estado $estado,
        ...$args
    )
    {
        $this->sigla = $sigla;
        $this->nome = $nome;
        $this->cidade = $cidade;
        $this->estado = $estado;
        parent::__construct(...$args);
    }

    public function getSigla(): SiglaAeroporto
    {
        return $this->sigla;
    }

    public function getNome(): string {
        return $this->nome;
    }

    public function getCidade(): string
    {
        return $this->cidade;
    }

    public function getEstado(): Estado
    {
        return $this->estado;
    }


    static public function getFilename(): string
    {
        return self::$local_filename;
    }
}
