<?php
// tests/Unit/TestFineModel.php
// MODELO: Fine (equivalente a "Multa")
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Fine;
use App\Models\Client;
use App\Models\Property;

uses(RefreshDatabase::class);

it('test_fine_puede_ser_creada_y_asociada', function () {
    $client = Client::create(attrsForModel(Client::class));
    $property = Property::create(array_merge(attrsForModel(Property::class), [
        'client_id' => $client->id,
        'cliente_id' => $client->id,
    ]));

    $fineAttrs = attrsForModel(Fine::class, [
        'monto' => 50.00,
        'descripcion' => 'Prueba fine',
    ]);
    // intentar setear FK si existen en fillable
    if (in_array('client_id', (new Fine)->getFillable())) $fineAttrs['client_id'] = $client->id;
    if (in_array('cliente_id', (new Fine)->getFillable())) $fineAttrs['cliente_id'] = $client->id;
    if (in_array('property_id', (new Fine)->getFillable())) $fineAttrs['property_id'] = $property->id;
    if (in_array('propiedad_id', (new Fine)->getFillable())) $fineAttrs['propiedad_id'] = $property->id;

    $fine = Fine::create($fineAttrs);
    expect($fine)->toBeInstanceOf(Fine::class)
                 ->and($fine->id)->not->toBeNull();
});
