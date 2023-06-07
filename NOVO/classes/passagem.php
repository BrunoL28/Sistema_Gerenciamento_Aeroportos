<?php

require_once 'identificadores.php';
require_once "HashMap.php";
require_once "Equatable.php";

enum Tipo: string
{
    use EnumToArray;

    case CANCELADA = "cancelada";
    case CHECK_IN_NAO_ABERTO = "check_in_nao_aberto";
    case AGUARDANDO_CHECK_IN = "aguardando_check_in";
    case NAO_APARECEU = "nao_apareceu";
    case CHECKED_IN = "checked_in";
    case EMBARCADO = "embarcado";
    case CONCLUIDA_COM_SUCESSO = "concluida_com_sucesso";
}

enum Evento: string
{
    use EnumToArray;

    case CANCELAR = "cancelar";
    case ABRIR_CHECK_IN = "abrir_check_in";
    case FAZER_CHECK_IN = "fazer_check_in";
    case EMBARCAR = "embarcar";
    case CONCLUIR = "concluir";
}

class StatusDaPassagem implements Equatable
{

    public Tipo $tipo;

    public function __construct(Tipo $tipo)
    {
        $this->tipo = $tipo;
    }

    public function cancelar(): StatusDaPassagem
    {
        return $this;
    }

    public function abrir_check_in(): StatusDaPassagem
    {
        return $this;
    }

    public function fazer_check_in(): StatusDaPassagem
    {
        return $this;
    }

    public function embarcar(): StatusDaPassagem
    {
        return $this;
    }

    public function concluir(): StatusDaPassagem
    {
        return $this;
    }

    public function dispatch_event(Evento $evento): StatusDaPassagem
    {
        switch ($evento) {
            case Evento::CANCELAR:
                return $this->cancelar();
            case Evento::ABRIR_CHECK_IN:
                return $this->abrir_check_in();
            case Evento::FAZER_CHECK_IN:
                return $this->fazer_check_in();
            case Evento::EMBARCAR:
                return $this->embarcar();
            case Evento::CONCLUIR:
                return $this->concluir();
        }
    }

    public function eq(Equatable $other): bool
    {
        if (!$other instanceof self) {
            throw new EquatableTypeException();
        }
        return $this->tipo == $other->tipo;
    }
}

class PassagemCancelada extends StatusDaPassagem
{
    public function __construct()
    {
        parent::__construct(Tipo::CANCELADA);
    }
}

class PassagemCheckInNaoAberto extends StatusDaPassagem
{
    public function __construct()
    {
        parent::__construct(Tipo::CHECK_IN_NAO_ABERTO);
    }

    // Transition to cancelled or to checkin aberto
    public function cancelar(): StatusDaPassagem
    {
        return new PassagemCancelada();
    }

    public function abrir_check_in(): StatusDaPassagem
    {
        return new PassagemAguardandoCheckIn();
    }
}

class PassagemAguardandoCheckIn extends StatusDaPassagem
{
    public function __construct()
    {
        parent::__construct(Tipo::AGUARDANDO_CHECK_IN);
    }

    // Transition to cancelled, checked in or did not show up
    public function cancelar(): StatusDaPassagem
    {
        return new PassagemCancelada();
    }

    public function fazer_check_in(): StatusDaPassagem
    {
        return new PassagemCheckedIn();
    }

    public function concluir(): StatusDaPassagem
    {
        return new PassagemNaoApareceu();
    }
}

class PassagemNaoApareceu extends StatusDaPassagem
{
    public function __construct()
    {
        parent::__construct(Tipo::NAO_APARECEU);
    }
    // Dont transition
}

class PassagemCheckedIn extends StatusDaPassagem
{
    public function __construct()
    {
        parent::__construct(Tipo::CHECKED_IN);
    }

    // Transition to no show, cancelled or embarcated
    public function embarcar(): StatusDaPassagem
    {
        return new PassagemEmbarcado();
    }

    public function cancelar(): StatusDaPassagem
    {
        return new PassagemCancelada();
    }

    public function concluir(): StatusDaPassagem
    {
        return new PassagemNaoApareceu();
    }
}

class PassagemEmbarcado extends StatusDaPassagem
{
    public function __construct()
    {
        parent::__construct(Tipo::EMBARCADO);
    }

    // Transition to concluded
    public function concluir(): StatusDaPassagem
    {
        return new PassagemConcluidaComSucesso();
    }
}

class PassagemConcluidaComSucesso extends StatusDaPassagem
{
    public function __construct()
    {
        parent::__construct(Tipo::CONCLUIDA_COM_SUCESSO);
    }
    // Dont transition
}

class Passagem
{
    private RegistroDePassagem $registro;
    private SiglaAeroporto $aeroporto_de_saida;
    private SiglaAeroporto $aeroporto_de_chegada;
    private SiglaCompanhiaAerea $companhia_aerea;
    private DocumentoPessoa $documento_cliente;
    private Data $data;
    private float $valor;
    private float $valor_pago;
    /** TODO: should be an tuple actually
     * @var HashMapEntry<RegistroDeViagem, CodigoDoAssento>[]
     */
    private array $assentos;
    private DataTempo $data_tempo_de_compra;
    private StatusDaPassagem $status;

    public function __construct(
        RegistroDePassagem  $registro,
        SiglaAeroporto      $aeroporto_de_saida,
        SiglaAeroporto      $aeroporto_de_chegada,
        SiglaCompanhiaAerea $companhia_aerea,
        DocumentoPessoa     $documento_cliente,
        Data                $data,
        float               $valor,
        float               $valor_pago,
        array               $assentos,
        DataTempo           $data_tempo_de_compra,
        StatusDaPassagem    $status
    )
    {
        $this->registro = $registro;
        $this->aeroporto_de_saida = $aeroporto_de_saida;
        $this->aeroporto_de_chegada = $aeroporto_de_chegada;
        $this->companhia_aerea = $companhia_aerea;
        $this->documento_cliente = $documento_cliente;
        $this->data = $data;
        $this->valor = $valor;
        $this->valor_pago = $valor_pago;
        $this->assentos = $assentos;
        $this->data_tempo_de_compra = $data_tempo_de_compra;
        $this->status = $status;
    }

    /**
     * @return RegistroDePassagem
     */
    public function getRegistro(): RegistroDePassagem
    {
        return $this->registro;
    }

    /**
     * @return SiglaAeroporto
     */
    public function getAeroportoDeSaida(): SiglaAeroporto
    {
        return $this->aeroporto_de_saida;
    }

    /**
     * @return SiglaAeroporto
     */
    public function getAeroportoDeChegada(): SiglaAeroporto
    {
        return $this->aeroporto_de_chegada;
    }

    /**
     * @return SiglaCompanhiaAerea
     */
    public function getCompanhiaAerea(): SiglaCompanhiaAerea
    {
        return $this->companhia_aerea;
    }

    /**
     * @return DocumentoPessoa
     */
    public function getDocumentoCliente(): DocumentoPessoa
    {
        return $this->documento_cliente;
    }

    /**
     * @return Data
     */
    public function getData(): Data
    {
        return $this->data;
    }

    /**
     * @return float
     */
    public function getValor(): float
    {
        return $this->valor;
    }

    /**
     * @return float
     */
    public function getValorPago(): float
    {
        return $this->valor_pago;
    }

    /**
     * @return HashMapEnry<RegistroDeViagem, CodigoDoAssento>[]
     */
    public function getAssentos(): array
    {
        return $this->assentos;
    }

    /**
     * @return DataTempo
     */
    public function getDataTempoDeCompra(): DataTempo
    {
        return $this->data_tempo_de_compra;
    }

    public function tipoDeStatus(): Tipo
    {
        return $this->status->tipo;
    }

    public function valorDevendo(): float
    {
        return $this->valor - $this->valor_pago;
    }

    public function pagar(float $valor): float
    {
        if ($this->valorDevendo() < $valor) {
            throw new Exception("Você está pagando muito, a companhia não pode ficar te devendo");
        }
        $this->valor_pago += $valor;
        return $this->valorDevendo();
    }

    public function acionarEvento(Evento $evento): bool
    {
        $old_status = $this->status;
        $new_status = $this->status->dispatch_event($evento);
        $this->status = $new_status;
        return $old_status !== $new_status;
    }
}
