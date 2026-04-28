<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;
use ApiPlatform\OpenApi\Model;

class CustomOpenApiFactory implements OpenApiFactoryInterface
{
    private OpenApiFactoryInterface $decorated;

    public function __construct(OpenApiFactoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        $openApi = $this->registerSecuritySchemes($openApi);

        // Ejemplo: agregar un endpoint custom a la documentación
        $openApi->getPaths()->addPath(
            '/api/bookings/occupied-seats',
            new Model\PathItem(
                get: new Model\Operation(
                    operationId: 'getOccupiedSeats',
                    tags: ['Bookings'],
                    summary: 'Obtener asientos ocupados en tiempo real',
                    description: 'Devuelve los asientos ocupados para un viaje específico.',
                    security: [['bearerAuth' => []]],
                    parameters: [
                        new Model\Parameter(
                            name: 'trip_id',
                            in: 'query',
                            required: true,
                            description: 'ID del viaje',
                            schema: ['type' => 'integer', 'example' => 1]
                        ),
                    ],
                    responses: [
                        '200' => new Model\Response(
                            description: 'Lista de asientos ocupados',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'object',
                                        'properties' => [
                                            'seats' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'seat' => ['type' => 'integer', 'example' => 1],
                                                        'qr'   => ['type' => 'string', 'example' => 'uuid-1']
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ])
                                )
                            ])
                        )
                    ]
                )
            )
        );

        // Endpoint: Registro de pasajero
        $openApi->getPaths()->addPath(
            '/api/auth/passenger/register',
            new Model\PathItem(
                post: new Model\Operation(
                    operationId: 'passengerRegister',
                    tags: ['Auth Passenger'],
                    summary: 'Registro de pasajero',
                    description: 'Registra un nuevo pasajero y devuelve un token.',
                    requestBody: new Model\RequestBody(
                        description: 'Datos de registro',
                        content: new \ArrayObject([
                            'application/json' => new Model\MediaType(
                                schema: new \ArrayObject([
                                    'type' => 'object',
                                    'required' => ['name', 'email', 'password', 'password_confirmation'],
                                    'properties' => [
                                        'name' => ['type' => 'string', 'example' => 'Juan Perez'],
                                        'email' => ['type' => 'string', 'format' => 'email', 'example' => 'juan@demo.com'],
                                        'password' => ['type' => 'string', 'example' => 'password123'],
                                        'password_confirmation' => ['type' => 'string', 'example' => 'password123'],
                                        'phone' => ['type' => 'string', 'example' => '+529991234567', 'nullable' => true],
                                    ],
                                ])
                            ),
                        ]),
                        required: true
                    ),
                    responses: [
                        '201' => new Model\Response(
                            description: 'Pasajero registrado exitosamente.',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'object',
                                        'properties' => [
                                            'passenger' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'id' => ['type' => 'integer', 'example' => 1],
                                                    'name' => ['type' => 'string', 'example' => 'Juan Perez'],
                                                    'email' => ['type' => 'string', 'example' => 'juan@demo.com'],
                                                    'phone' => ['type' => 'string', 'example' => '+529991234567', 'nullable' => true],
                                                ]
                                            ],
                                            'token' => ['type' => 'string', 'example' => '1|abcdef1234567890'],
                                        ]
                                    ])
                                )
                            ])
                        ),
                        '422' => new Model\Response('Error de validación.')
                    ]
                )
            )
        );

        // Endpoint: Login de pasajero
        $openApi->getPaths()->addPath(
            '/api/auth/passenger/login',
            new Model\PathItem(
                post: new Model\Operation(
                    operationId: 'passengerLogin',
                    tags: ['Auth Passenger'],
                    summary: 'Login de pasajero',
                    description: 'Autentica a un pasajero y devuelve un token.',
                    requestBody: new Model\RequestBody(
                        description: 'Credenciales de login',
                        content: new \ArrayObject([
                            'application/json' => new Model\MediaType(
                                schema: new \ArrayObject([
                                    'type' => 'object',
                                    'required' => ['email', 'password'],
                                    'properties' => [
                                        'email' => ['type' => 'string', 'format' => 'email', 'example' => 'juan@demo.com'],
                                        'password' => ['type' => 'string', 'example' => 'password123'],
                                    ],
                                ])
                            ),
                        ]),
                        required: true
                    ),
                    responses: [
                        '200' => new Model\Response(
                            description: 'Token generado exitosamente.',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'object',
                                        'properties' => [
                                            'passenger' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'id' => ['type' => 'integer', 'example' => 1],
                                                    'name' => ['type' => 'string', 'example' => 'Juan Perez'],
                                                    'email' => ['type' => 'string', 'example' => 'juan@demo.com'],
                                                    'phone' => ['type' => 'string', 'example' => '+529991234567', 'nullable' => true],
                                                ]
                                            ],
                                            'token' => ['type' => 'string', 'example' => '1|abcdef1234567890'],
                                        ]
                                    ])
                                )
                            ])
                        ),
                        '422' => new Model\Response('Credenciales incorrectas.')
                    ]
                )
            )
        );

        // Endpoint: Viajes disponibles
        $openApi->getPaths()->addPath(
            '/api/bookings/available-trips',
            new Model\PathItem(
                get: new Model\Operation(
                    operationId: 'getAvailableTrips',
                    tags: ['Bookings'],
                    summary: 'Listar viajes disponibles',
                    description: 'Devuelve la lista de viajes activos y futuros con su vehicle, ruta y asientos disponibles/ocupados. Permite filtrar por route_id, vehicle_id, status, date, from, to y paginar con per_page.',
                    security: [['bearerAuth' => []]],
                    parameters: [
                        new Model\Parameter(
                            name: 'route_id',
                            in: 'query',
                            required: false,
                            description: 'Filtrar por ID de ruta',
                            schema: ['type' => 'integer', 'example' => 1]
                        ),
                        new Model\Parameter(
                            name: 'vehicle_id',
                            in: 'query',
                            required: false,
                            description: 'Filtrar por ID de vehículo',
                            schema: ['type' => 'integer', 'example' => 2]
                        ),
                        new Model\Parameter(
                            name: 'status',
                            in: 'query',
                            required: false,
                            description: 'Filtrar por estado del viaje',
                            schema: ['type' => 'string', 'example' => 'assigned']
                        ),
                        new Model\Parameter(
                            name: 'date',
                            in: 'query',
                            required: false,
                            description: 'Filtrar por fecha exacta (YYYY-MM-DD)',
                            schema: ['type' => 'string', 'format' => 'date', 'example' => '2026-04-25']
                        ),
                        new Model\Parameter(
                            name: 'from',
                            in: 'query',
                            required: false,
                            description: 'Filtrar viajes desde esta fecha/hora (>=)',
                            schema: ['type' => 'string', 'format' => 'date-time', 'example' => '2026-04-25T00:00:00Z']
                        ),
                        new Model\Parameter(
                            name: 'to',
                            in: 'query',
                            required: false,
                            description: 'Filtrar viajes hasta esta fecha/hora (<=)',
                            schema: ['type' => 'string', 'format' => 'date-time', 'example' => '2026-04-25T23:59:59Z']
                        ),
                        new Model\Parameter(
                            name: 'page',
                            in: 'query',
                            required: false,
                            description: 'Número de página',
                            schema: ['type' => 'integer', 'example' => 1]
                        ),
                        new Model\Parameter(
                            name: 'per_page',
                            in: 'query',
                            required: false,
                            description: 'Cantidad de resultados por página',
                            schema: ['type' => 'integer', 'example' => 15]
                        ),
                    ],
                    responses: [
                        '200' => new Model\Response(
                            description: 'Lista de viajes disponibles',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'id' => ['type' => 'integer', 'example' => 1],
                                                'van_id' => ['type' => 'integer', 'example' => 2],
                                                'route_id' => ['type' => 'integer', 'example' => 3],
                                                'status' => ['type' => 'string', 'example' => 'active'],
                                                'datetime' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-04-21T10:00:00Z'],
                                                'vehicle' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'id' => ['type' => 'integer', 'example' => 2],
                                                        'plate' => ['type' => 'string', 'example' => 'ABC-123'],
                                                        'capacity' => ['type' => 'integer', 'example' => 15],
                                                    ]
                                                ],
                                                'route' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'id' => ['type' => 'integer', 'example' => 3],
                                                        'name' => ['type' => 'string', 'example' => 'Ruta Centro'],
                                                    ]
                                                ],
                                                'available_seats' => [
                                                    'type' => 'array',
                                                    'items' => ['type' => 'integer', 'example' => 5]
                                                ],
                                                'occupied_seats' => [
                                                    'type' => 'array',
                                                    'items' => ['type' => 'integer', 'example' => 2]
                                                ],
                                                'capacity' => ['type' => 'integer', 'example' => 15]
                                            ]
                                        ]
                                    ])
                                )
                            ])
                        )
                    ]
                )
            )
        );

        // Endpoint: Crear reserva (store)
        $openApi->getPaths()->addPath(
            '/api/bookings',
            new Model\PathItem(
                post: new Model\Operation(
                    operationId: 'createBooking',
                    tags: ['Bookings'],
                    summary: 'Crear una reserva',
                    description: 'Crea una reserva para uno o más asientos en un viaje.',
                    security: [['bearerAuth' => []]],
                    requestBody: new Model\RequestBody(
                        description: 'Datos de la reserva',
                        content: new \ArrayObject([
                            'application/json' => new Model\MediaType(
                                schema: new \ArrayObject([
                                    'type' => 'object',
                                    'required' => ['trip_id', 'passenger_id', 'seats'],
                                    'properties' => [
                                        'trip_id' => ['type' => 'integer', 'example' => 1],
                                        'passenger_id' => ['type' => 'integer', 'example' => 5],
                                        'seats' => [
                                            'type' => 'array',
                                            'items' => ['type' => 'integer', 'example' => 2]
                                        ]
                                    ]
                                ])
                            )
                        ]),
                        required: true
                    ),
                    responses: [
                        '201' => new Model\Response(
                            description: 'Reserva creada exitosamente.',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'object',
                                        'properties' => [
                                            'id' => ['type' => 'integer', 'example' => 10],
                                            'trip_id' => ['type' => 'integer', 'example' => 1],
                                            'passenger_id' => ['type' => 'integer', 'example' => 5],
                                            'status' => ['type' => 'string', 'example' => 'active'],
                                            'seats' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'seat' => ['type' => 'integer', 'example' => 2],
                                                        'qr' => ['type' => 'string', 'example' => 'uuid-1']
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ])
                                )
                            ])
                        ),
                        '409' => new Model\Response('Uno o más asientos ya están ocupados.'),
                        '422' => new Model\Response('Error de validación.')
                    ]
                )
            )
        );
        // Endpoint: Update booking
        $openApi->getPaths()->addPath(
            '/api/bookings/{booking}',
            new Model\PathItem(
                patch: new Model\Operation(
                    operationId: 'updateBooking',
                    tags: ['Bookings'],
                    summary: 'Update a booking',
                    description: 'Update a booking (e.g., change seat or status).',
                    security: [['bearerAuth' => []]],
                    parameters: [
                        new Model\Parameter(
                            name: 'booking',
                            in: 'path',
                            required: true,
                            description: 'Booking ID',
                            schema: ['type' => 'integer', 'example' => 10]
                        ),
                    ],
                    requestBody: new Model\RequestBody(
                        description: 'Booking update data',
                        content: new \ArrayObject([
                            'application/json' => new Model\MediaType(
                                schema: new \ArrayObject([
                                    'type' => 'object',
                                    'properties' => [
                                        'seat' => ['type' => 'integer', 'example' => 4],
                                        'status' => ['type' => 'string', 'example' => 'cancelled']
                                    ]
                                ])
                            )
                        ]),
                        required: true
                    ),
                    responses: [
                        '200' => new Model\Response('Booking updated'),
                        '404' => new Model\Response('Booking not found'),
                        '422' => new Model\Response('Validation error')
                    ]
                ),
                delete: new Model\Operation(
                    operationId: 'deleteBooking',
                    tags: ['Bookings'],
                    summary: 'Cancel a booking',
                    description: 'Cancel a booking by ID.',
                    security: [['bearerAuth' => []]],
                    parameters: [
                        new Model\Parameter(
                            name: 'booking',
                            in: 'path',
                            required: true,
                            description: 'Booking ID',
                            schema: ['type' => 'integer', 'example' => 10]
                        ),
                    ],
                    responses: [
                        '200' => new Model\Response('Booking cancelled'),
                        '404' => new Model\Response('Booking not found')
                    ]
                )
            )
        );

        // Endpoint: Trip chat send
        $openApi->getPaths()->addPath(
            '/api/trips/{trip}/chat',
            new Model\PathItem(
                post: new Model\Operation(
                    operationId: 'sendTripChat',
                    tags: ['Trip Chat'],
                    summary: 'Send a message to trip chat',
                    description: 'Send a chat message to a trip.',
                    security: [['bearerAuth' => []]],
                    parameters: [
                        new Model\Parameter(
                            name: 'trip',
                            in: 'path',
                            required: true,
                            description: 'Trip ID',
                            schema: ['type' => 'integer', 'example' => 5]
                        ),
                    ],
                    requestBody: new Model\RequestBody(
                        description: 'Chat message data',
                        content: new \ArrayObject([
                            'application/json' => new Model\MediaType(
                                schema: new \ArrayObject([
                                    'type' => 'object',
                                    'properties' => [
                                        'user_id' => ['type' => 'integer', 'example' => 1],
                                        'user_name' => ['type' => 'string', 'example' => 'John'],
                                        'message' => ['type' => 'string', 'example' => 'Hello!']
                                    ]
                                ])
                            )
                        ]),
                        required: true
                    ),
                    responses: [
                        '200' => new Model\Response('Message sent'),
                        '404' => new Model\Response('Trip not found'),
                        '422' => new Model\Response('Validation error')
                    ]
                ),
                get: new Model\Operation(
                    operationId: 'getTripChatHistory',
                    tags: ['Trip Chat'],
                    summary: 'Get trip chat history',
                    description: 'Get chat messages for a trip.',
                    security: [['bearerAuth' => []]],
                    parameters: [
                        new Model\Parameter(
                            name: 'trip',
                            in: 'path',
                            required: true,
                            description: 'Trip ID',
                            schema: ['type' => 'integer', 'example' => 5]
                        ),
                    ],
                    responses: [
                        '200' => new Model\Response('Chat history'),
                        '404' => new Model\Response('Trip not found')
                    ]
                )
            )
        );

        // Endpoint: Trip presence sync
        $openApi->getPaths()->addPath(
            '/api/trips/{trip}/presence/sync',
            new Model\PathItem(
                post: new Model\Operation(
                    operationId: 'syncTripPresence',
                    tags: ['Trip Presence'],
                    summary: 'Sync trip presence',
                    description: 'Sync connected users to a trip (presence channel).',
                    security: [['bearerAuth' => []]],
                    parameters: [
                        new Model\Parameter(
                            name: 'trip',
                            in: 'path',
                            required: true,
                            description: 'Trip ID',
                            schema: ['type' => 'integer', 'example' => 5]
                        ),
                    ],
                    requestBody: new Model\RequestBody(
                        description: 'Presence data',
                        content: new \ArrayObject([
                            'application/json' => new Model\MediaType(
                                schema: new \ArrayObject([
                                    'type' => 'object',
                                    'properties' => [
                                        'users' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'id' => ['type' => 'integer', 'example' => 1],
                                                    'name' => ['type' => 'string', 'example' => 'John']
                                                ]
                                            ]
                                        ],
                                        'type' => ['type' => 'string', 'example' => 'join']
                                    ]
                                ])
                            )
                        ]),
                        required: true
                    ),
                    responses: [
                        '200' => new Model\Response('Presence synced'),
                        '404' => new Model\Response('Trip not found'),
                        '422' => new Model\Response('Validation error')
                    ]
                )
            )
        );

        $openApi->getPaths()->addPath(
            '/api/gps/update',
            new Model\PathItem(
                post: new Model\Operation(
                    operationId: 'gpsUpdate',
                    tags: ['GPS'],
                    summary: 'Actualizar ubicación GPS',
                    description: 'Recibe y almacena una nueva ubicación GPS de un dispositivo.',
                    requestBody: new Model\RequestBody(
                        description: 'Datos de ubicación',
                        content: new \ArrayObject([
                            'application/json' => new Model\MediaType(
                                schema: new \ArrayObject([
                                    'type' => 'object',
                                    'required' => ['imei', 'latitude', 'longitude', 'timestamp'],
                                    'properties' => [
                                        'imei' => ['type' => 'string', 'example' => '123456789012345'],
                                        'latitude' => ['type' => 'number', 'format' => 'float', 'example' => -34.6037],
                                        'longitude' => ['type' => 'number', 'format' => 'float', 'example' => -58.3816],
                                        'altitude' => ['type' => 'integer', 'example' => 25],
                                        'speed' => ['type' => 'number', 'format' => 'float', 'example' => 45.5],
                                        'course' => ['type' => 'integer', 'example' => 90],
                                        'accuracy' => ['type' => 'number', 'format' => 'float', 'example' => 5.2],
                                        'satellites' => ['type' => 'integer', 'example' => 12],
                                        'battery' => ['type' => 'integer', 'example' => 85],
                                        'signal' => ['type' => 'integer', 'example' => 25],
                                        'timestamp' => ['type' => 'integer', 'example' => 1713800000]
                                    ]
                                ])
                            )
                        ]),
                        required: true
                    ),
                    responses: [
                        '200' => new Model\Response(
                            description: 'Ubicación actualizada',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean', 'example' => true],
                                            'message' => ['type' => 'string', 'example' => 'Location updated'],
                                            'data' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'location_id' => ['type' => 'integer', 'example' => 1],
                                                    'device_id' => ['type' => 'integer', 'example' => 1]
                                                ]
                                            ]
                                        ]
                                    ])
                                )
                            ])
                        ),
                        '422' => new Model\Response('Error de validación.')
                    ]
                )
            )
        );
        // Endpoint: Obtener todas las ubicaciones de vehículos
        $openApi->getPaths()->addPath(
            '/api/tracking/vehicles/all-locations',
            new Model\PathItem(
                get: new Model\Operation(
                    operationId: 'getAllVehiclesLocations',
                    tags: ['Tracking'],
                    summary: 'Obtener todas las ubicaciones de vehículos',
                    description: 'Devuelve la ubicación actual de todos los vehículos registrados en el sistema.',
                    security: [['bearerAuth' => []]],
                    responses: [
                        '200' => new Model\Response(
                            description: 'Lista de ubicaciones de vehículos',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'id' => ['type' => 'integer', 'example' => 1],
                                                'plate' => ['type' => 'string', 'example' => 'ABC-123'],
                                                'driver' => ['type' => 'string', 'example' => 'Juan Perez'],
                                                'device' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'id' => ['type' => 'integer', 'example' => 1],
                                                        'status' => ['type' => 'string', 'example' => 'active']
                                                    ]
                                                ],
                                                'location' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'latitude' => ['type' => 'number', 'format' => 'float', 'example' => -34.6037],
                                                        'longitude' => ['type' => 'number', 'format' => 'float', 'example' => -58.3816],
                                                        'speed' => ['type' => 'number', 'format' => 'float', 'example' => 45.5],
                                                        'recorded_at' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-04-22T12:00:00Z']
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ])
                                )
                            ])
                        )
                    ]
                )
            )
        );
        // --- Endpoints de Tracking ---
        // 1. Obtener estado de dispositivo GPS
        $openApi->getPaths()->addPath(
            '/api/gps/device/{imei}/status',
            new Model\PathItem(
                get: new Model\Operation(
                    operationId: 'getDeviceStatus',
                    tags: ['GPS'],
                    summary: 'Obtener estado del dispositivo GPS',
                    description: 'Devuelve el estado actual del dispositivo GPS por IMEI.',
                    parameters: [
                        new Model\Parameter(
                            name: 'imei',
                            in: 'path',
                            required: true,
                            description: 'IMEI del dispositivo',
                            schema: ['type' => 'string', 'example' => '123456789012345']
                        ),
                    ],
                    responses: [
                        '200' => new Model\Response(
                            description: 'Estado del dispositivo',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'object',
                                        'properties' => [
                                            'imei' => ['type' => 'string', 'example' => '123456789012345'],
                                            'status' => ['type' => 'string', 'example' => 'active'],
                                            'last_update' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-04-22T12:00:00Z']
                                        ]
                                    ])
                                )
                            ])
                        ),
                        '404' => new Model\Response('Dispositivo no encontrado')
                    ]
                )
            )
        );

        // 2. Obtener ubicación actual de un vehículo
        $openApi->getPaths()->addPath(
            '/api/tracking/vehicles/{vehicleId}/current-location',
            new Model\PathItem(
                get: new Model\Operation(
                    operationId: 'getVehicleCurrentLocation',
                    tags: ['Tracking'],
                    summary: 'Obtener ubicación actual de un vehículo',
                    description: 'Devuelve la última ubicación registrada para el vehículo.',
                    security: [['bearerAuth' => []]],
                    parameters: [
                        new Model\Parameter(
                            name: 'vehicleId',
                            in: 'path',
                            required: true,
                            description: 'ID del vehículo',
                            schema: ['type' => 'integer', 'example' => 1]
                        ),
                    ],
                    responses: [
                        '200' => new Model\Response(
                            description: 'Ubicación actual',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'object',
                                        'properties' => [
                                            'latitude' => ['type' => 'number', 'format' => 'float', 'example' => -34.6037],
                                            'longitude' => ['type' => 'number', 'format' => 'float', 'example' => -58.3816],
                                            'speed' => ['type' => 'number', 'format' => 'float', 'example' => 45.5],
                                            'recorded_at' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-04-22T12:00:00Z']
                                        ]
                                    ])
                                )
                            ])
                        ),
                        '404' => new Model\Response('Vehículo no encontrado')
                    ]
                )
            )
        );

        // 3. Obtener historial de ubicaciones
        $openApi->getPaths()->addPath(
            '/api/tracking/vehicles/{vehicleId}/route-history',
            new Model\PathItem(
                get: new Model\Operation(
                    operationId: 'getVehicleRouteHistory',
                    tags: ['Tracking'],
                    summary: 'Obtener historial de ubicaciones de un vehículo',
                    description: 'Devuelve el historial de ubicaciones para el vehículo en un rango de fechas.',
                    security: [['bearerAuth' => []]],
                    parameters: [
                        new Model\Parameter(
                            name: 'vehicleId',
                            in: 'path',
                            required: true,
                            description: 'ID del vehículo',
                            schema: ['type' => 'integer', 'example' => 1]
                        ),
                        new Model\Parameter(
                            name: 'start_date',
                            in: 'query',
                            required: false,
                            description: 'Fecha inicio (YYYY-MM-DD)',
                            schema: ['type' => 'string', 'format' => 'date', 'example' => '2024-01-01']
                        ),
                        new Model\Parameter(
                            name: 'end_date',
                            in: 'query',
                            required: false,
                            description: 'Fecha fin (YYYY-MM-DD)',
                            schema: ['type' => 'string', 'format' => 'date', 'example' => '2024-01-02']
                        ),
                    ],
                    responses: [
                        '200' => new Model\Response(
                            description: 'Historial de ubicaciones',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'latitude' => ['type' => 'number', 'format' => 'float', 'example' => -34.6037],
                                                'longitude' => ['type' => 'number', 'format' => 'float', 'example' => -58.3816],
                                                'speed' => ['type' => 'number', 'format' => 'float', 'example' => 45.5],
                                                'recorded_at' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-04-22T12:00:00Z']
                                            ]
                                        ]
                                    ])
                                )
                            ])
                        ),
                        '404' => new Model\Response('Vehículo no encontrado')
                    ]
                )
            )
        );

        // 4. Obtener viajes de un vehículo
        $openApi->getPaths()->addPath(
            '/api/tracking/vehicles/{vehicleId}/trips',
            new Model\PathItem(
                get: new Model\Operation(
                    operationId: 'getVehicleTrips',
                    tags: ['Tracking'],
                    summary: 'Obtener viajes de un vehículo',
                    description: 'Devuelve la lista de viajes asociados al vehículo.',
                    security: [['bearerAuth' => []]],
                    parameters: [
                        new Model\Parameter(
                            name: 'vehicleId',
                            in: 'path',
                            required: true,
                            description: 'ID del vehículo',
                            schema: ['type' => 'integer', 'example' => 1]
                        ),
                        new Model\Parameter(
                            name: 'start_date',
                            in: 'query',
                            required: false,
                            description: 'Fecha inicio (YYYY-MM-DD)',
                            schema: ['type' => 'string', 'format' => 'date', 'example' => '2024-01-01']
                        ),
                        new Model\Parameter(
                            name: 'end_date',
                            in: 'query',
                            required: false,
                            description: 'Fecha fin (YYYY-MM-DD)',
                            schema: ['type' => 'string', 'format' => 'date', 'example' => '2024-01-02']
                        ),
                    ],
                    responses: [
                        '200' => new Model\Response(
                            description: 'Lista de viajes',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'id' => ['type' => 'integer', 'example' => 10],
                                                'start_time' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-04-22T08:00:00Z'],
                                                'end_time' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-04-22T09:00:00Z'],
                                                'distance' => ['type' => 'number', 'format' => 'float', 'example' => 12.5]
                                            ]
                                        ]
                                    ])
                                )
                            ])
                        ),
                        '404' => new Model\Response('Vehículo no encontrado')
                    ]
                )
            )
        );

        // 5. Obtener paradas de un vehículo
        $openApi->getPaths()->addPath(
            '/api/tracking/vehicles/{vehicleId}/stops',
            new Model\PathItem(
                get: new Model\Operation(
                    operationId: 'getVehicleStops',
                    tags: ['Tracking'],
                    summary: 'Obtener paradas de un vehículo',
                    description: 'Devuelve la lista de paradas detectadas para el vehículo.',
                    security: [['bearerAuth' => []]],
                    parameters: [
                        new Model\Parameter(
                            name: 'vehicleId',
                            in: 'path',
                            required: true,
                            description: 'ID del vehículo',
                            schema: ['type' => 'integer', 'example' => 1]
                        ),
                        new Model\Parameter(
                            name: 'start_date',
                            in: 'query',
                            required: false,
                            description: 'Fecha inicio (YYYY-MM-DD)',
                            schema: ['type' => 'string', 'format' => 'date', 'example' => '2024-01-01']
                        ),
                        new Model\Parameter(
                            name: 'end_date',
                            in: 'query',
                            required: false,
                            description: 'Fecha fin (YYYY-MM-DD)',
                            schema: ['type' => 'string', 'format' => 'date', 'example' => '2024-01-02']
                        ),
                        new Model\Parameter(
                            name: 'min_duration',
                            in: 'query',
                            required: false,
                            description: 'Duración mínima de la parada en segundos',
                            schema: ['type' => 'integer', 'example' => 300]
                        ),
                    ],
                    responses: [
                        '200' => new Model\Response(
                            description: 'Lista de paradas',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'latitude' => ['type' => 'number', 'format' => 'float', 'example' => -34.6037],
                                                'longitude' => ['type' => 'number', 'format' => 'float', 'example' => -58.3816],
                                                'stopped_at' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-04-22T10:00:00Z'],
                                                'duration' => ['type' => 'integer', 'example' => 300]
                                            ]
                                        ]
                                    ])
                                )
                            ])
                        ),
                        '404' => new Model\Response('Vehículo no encontrado')
                    ]
                )
            )
        );
        // Alta de dispositivo GPS
        $openApi->getPaths()->addPath(
            '/api/gps/device',
            new Model\PathItem(
                post: new Model\Operation(
                    operationId: 'createGpsDevice',
                    tags: ['GPS'],
                    summary: 'Registrar un nuevo dispositivo GPS',
                    description: 'Crea un nuevo registro de GpsDevice asociado a un vehículo.',
                    requestBody: new Model\RequestBody(
                        description: 'Datos del dispositivo GPS',
                        content: new \ArrayObject([
                            'application/json' => new Model\MediaType(
                                schema: new \ArrayObject([
                                    'type' => 'object',
                                    'required' => ['vehicle_id', 'imei', 'device_name'],
                                    'properties' => [
                                        'vehicle_id' => ['type' => 'integer', 'example' => 1],
                                        'imei' => ['type' => 'string', 'example' => '123456789012345'],
                                        'device_name' => ['type' => 'string', 'example' => 'Dispositivo_001'],
                                        'device_model' => ['type' => 'string', 'example' => 'TK102'],
                                        'device_brand' => ['type' => 'string', 'example' => 'Generic'],
                                        'status' => ['type' => 'string', 'example' => 'active'],
                                        'battery_level' => ['type' => 'integer', 'example' => 85],
                                        'gps_update_interval' => ['type' => 'integer', 'example' => 30],
                                    ]
                                ])
                            )
                        ])
                    ),
                    responses: [
                        '201' => new Model\Response(
                            description: 'GpsDevice creado',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean', 'example' => true],
                                            'message' => ['type' => 'string', 'example' => 'GpsDevice creado'],
                                            'data' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'id' => ['type' => 'integer', 'example' => 1],
                                                    'vehicle_id' => ['type' => 'integer', 'example' => 1],
                                                    'imei' => ['type' => 'string', 'example' => '123456789012345'],
                                                    'device_name' => ['type' => 'string', 'example' => 'Dispositivo_001'],
                                                    'device_model' => ['type' => 'string', 'example' => 'TK102'],
                                                    'device_brand' => ['type' => 'string', 'example' => 'Generic'],
                                                    'status' => ['type' => 'string', 'example' => 'active'],
                                                    'battery_level' => ['type' => 'integer', 'example' => 85],
                                                    'gps_update_interval' => ['type' => 'integer', 'example' => 30],
                                                ]
                                            ]
                                        ]
                                    ])
                                )
                            ])
                        ),
                        '422' => new Model\Response('Error de validación.')
                    ]
                )
            )
        );
        // Endpoint: Listar rutas
        $openApi->getPaths()->addPath(
            '/api/routes',
            new Model\PathItem(
                get: new Model\Operation(
                    operationId: 'getRoutes',
                    tags: ['Routes'],
                    summary: 'Listar rutas',
                    description: 'Devuelve la lista de todas las rutas.',
                    parameters: [
                        new Model\Parameter(
                            name: 'search',
                            in: 'query',
                            required: false,
                            description: 'Buscar rutas por nombre',
                            schema: ['type' => 'string', 'example' => 'centro']
                        ),
                        new Model\Parameter(
                            name: 'page',
                            in: 'query',
                            required: false,
                            description: 'Número de página',
                            schema: ['type' => 'integer', 'example' => 1]
                        ),
                        new Model\Parameter(
                            name: 'per_page',
                            in: 'query',
                            required: false,
                            description: 'Resultados por página',
                            schema: ['type' => 'integer', 'example' => 15]
                        ),
                    ],
                    responses: [
                        '200' => new Model\Response(
                            description: 'Lista de rutas',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'object',
                                        'properties' => [
                                            'routes' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'id' => ['type' => 'integer', 'example' => 1],
                                                        'name' => ['type' => 'string', 'example' => 'Ruta Centro'],
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ])
                                )
                            ])
                        )
                    ]
                )
            )
        );
        // Endpoint: Paradas de una ruta
        $openApi->getPaths()->addPath(
            '/api/routes/{id}/stops',
            new Model\PathItem(
                get: new Model\Operation(
                    operationId: 'getRouteStops',
                    tags: ['Routes'],
                    summary: 'Obtener paradas de una ruta',
                    description: 'Devuelve la lista de paradas asociadas a una ruta por su id. Permite buscar por nombre y paginar los resultados.',
                    parameters: [
                        new Model\Parameter(
                            name: 'id',
                            in: 'path',
                            required: true,
                            description: 'ID de la ruta',
                            schema: ['type' => 'integer', 'example' => 1]
                        ),
                        new Model\Parameter(
                            name: 'search',
                            in: 'query',
                            required: false,
                            description: 'Buscar paradas por nombre',
                            schema: ['type' => 'string', 'example' => 'central']
                        ),
                        new Model\Parameter(
                            name: 'page',
                            in: 'query',
                            required: false,
                            description: 'Número de página',
                            schema: ['type' => 'integer', 'example' => 1]
                        ),
                        new Model\Parameter(
                            name: 'per_page',
                            in: 'query',
                            required: false,
                            description: 'Resultados por página',
                            schema: ['type' => 'integer', 'example' => 15]
                        ),
                    ],
                    responses: [
                        '200' => new Model\Response(
                            description: 'Lista de paradas',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'object',
                                        'properties' => [
                                            'route_id' => ['type' => 'integer', 'example' => 1],
                                            'stops' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'id' => ['type' => 'integer', 'example' => 10],
                                                        'name' => ['type' => 'string', 'example' => 'Parada Central'],
                                                        'lat' => ['type' => 'number', 'format' => 'float', 'example' => 21.12345],
                                                        'lng' => ['type' => 'number', 'format' => 'float', 'example' => -89.12345],
                                                        'order' => ['type' => 'integer', 'example' => 1],
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ])
                                )
                            ])
                        )
                    ]
                )
            )
        );

        // Endpoint: Horarios de una ruta
        $openApi->getPaths()->addPath(
            '/api/routes/{id}/schedules',
            new Model\PathItem(
                get: new Model\Operation(
                    operationId: 'getRouteSchedules',
                    tags: ['Routes'],
                    summary: 'Obtener horarios de una ruta',
                    description: 'Devuelve la lista de horarios asociados a una ruta por su id.',
                    parameters: [
                        new Model\Parameter(
                            name: 'id',
                            in: 'path',
                            required: true,
                            description: 'ID de la ruta',
                            schema: ['type' => 'integer', 'example' => 1]
                        ),
                        new Model\Parameter(
                            name: 'search',
                            in: 'query',
                            required: false,
                            description: 'Buscar horarios por hora de salida',
                            schema: ['type' => 'string', 'example' => '08:00']
                        ),
                        new Model\Parameter(
                            name: 'page',
                            in: 'query',
                            required: false,
                            description: 'Número de página',
                            schema: ['type' => 'integer', 'example' => 1]
                        ),
                        new Model\Parameter(
                            name: 'per_page',
                            in: 'query',
                            required: false,
                            description: 'Resultados por página',
                            schema: ['type' => 'integer', 'example' => 15]
                        ),
                    ],
                    responses: [
                        '200' => new Model\Response(
                            description: 'Lista de horarios',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'object',
                                        'properties' => [
                                            'route_id' => ['type' => 'integer', 'example' => 1],
                                            'schedules' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'id' => ['type' => 'integer', 'example' => 5],
                                                        'departure_time' => ['type' => 'string', 'format' => 'time', 'example' => '08:00:00'],
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ])
                                )
                            ])
                        )
                    ]
                )
            )
        );
        // --- Endpoints de reservas y viajes del pasajero ---
        $openApi->getPaths()->addPath(
            '/api/reservations',
            new Model\PathItem(
                post: new Model\Operation(
                    operationId: 'createPassengerReservation',
                    tags: ['Passenger Reservations'],
                    summary: 'Crear una reservación',
                    description: 'Crea una reservación para el pasajero autenticado.',
                    security: [['bearerAuth' => []]],
                    requestBody: new Model\RequestBody(
                        description: 'Datos de la reservación',
                        content: new \ArrayObject([
                            'application/json' => new Model\MediaType(
                                schema: new \ArrayObject([
                                    'type' => 'object',
                                    'required' => ['trip_id', 'seats'],
                                    'properties' => [
                                        'trip_id' => ['type' => 'integer', 'example' => 1],
                                        'seats' => [
                                            'type' => 'array',
                                            'items' => ['type' => 'integer', 'example' => 2]
                                        ]
                                    ]
                                ])
                            )
                        ]),
                        required: true
                    ),
                    responses: [
                        '201' => new Model\Response(
                            description: 'Reservación creada exitosamente.',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'object',
                                        'properties' => [
                                            'id' => ['type' => 'integer', 'example' => 10],
                                            'trip_id' => ['type' => 'integer', 'example' => 1],
                                            'status' => ['type' => 'string', 'example' => 'active'],
                                            'seats' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'seat' => ['type' => 'integer', 'example' => 2],
                                                        'qr' => ['type' => 'string', 'example' => 'uuid-1']
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ])
                                )
                            ])
                        ),
                        '409' => new Model\Response('Uno o más asientos ya están ocupados.'),
                        '422' => new Model\Response('Error de validación.')
                    ]
                )
            )
        );

        $openApi->getPaths()->addPath(
            '/api/trips',
            new Model\PathItem(
                get: new Model\Operation(
                    operationId: 'getMyTrips',
                    tags: ['Passenger Reservations'],
                    summary: 'Listar mis viajes',
                    description: 'Devuelve todos los viajes reservados por el pasajero autenticado. Permite filtrar por estado.',
                    security: [['bearerAuth' => []]],
                    parameters: [
                        new Model\Parameter(
                            name: 'status',
                            in: 'query',
                            required: false,
                            description: 'Filtrar por estado de la reservación',
                            schema: ['type' => 'string', 'example' => 'active']
                        ),
                        new Model\Parameter(
                            name: 'per_page',
                            in: 'query',
                            required: false,
                            description: 'Cantidad de resultados por página',
                            schema: ['type' => 'integer', 'example' => 15]
                        ),
                        new Model\Parameter(
                            name: 'page',
                            in: 'query',
                            required: false,
                            description: 'Número de página',
                            schema: ['type' => 'integer', 'example' => 1]
                        ),
                    ],
                    responses: [
                        '200' => new Model\Response(
                            description: 'Lista de viajes reservados',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'booking_id' => ['type' => 'integer', 'example' => 10],
                                                'status' => ['type' => 'string', 'example' => 'active'],
                                                'seats' => [
                                                    'type' => 'array',
                                                    'items' => [
                                                        'type' => 'object',
                                                        'properties' => [
                                                            'seat' => ['type' => 'integer', 'example' => 2],
                                                            'qr' => ['type' => 'string', 'example' => 'uuid-1']
                                                        ]
                                                    ]
                                                ],
                                                'trip' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'id' => ['type' => 'integer', 'example' => 1],
                                                        'datetime' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-04-21T10:00:00Z'],
                                                        'status' => ['type' => 'string', 'example' => 'assigned'],
                                                        'vehicle' => ['type' => 'object'],
                                                        'route' => ['type' => 'object'],
                                                    ]
                                                ],
                                                'created_at' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-04-21T09:00:00Z']
                                            ]
                                        ]
                                    ])
                                )
                            ])
                        )
                    ]
                )
            )
        );

        $openApi->getPaths()->addPath(
            '/api/trips/next',
            new Model\PathItem(
                get: new Model\Operation(
                    operationId: 'getNextTrip',
                    tags: ['Passenger Reservations'],
                    summary: 'Obtener el próximo viaje',
                    description: 'Devuelve el próximo viaje activo y futuro del pasajero autenticado.',
                    security: [['bearerAuth' => []]],
                    responses: [
                        '200' => new Model\Response(
                            description: 'Próximo viaje',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'object',
                                        'properties' => [
                                            'booking_id' => ['type' => 'integer', 'example' => 10],
                                            'status' => ['type' => 'string', 'example' => 'active'],
                                            'seats' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'seat' => ['type' => 'integer', 'example' => 2],
                                                        'qr' => ['type' => 'string', 'example' => 'uuid-1']
                                                    ]
                                                ]
                                            ],
                                            'trip' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'id' => ['type' => 'integer', 'example' => 1],
                                                    'datetime' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-04-21T10:00:00Z'],
                                                    'status' => ['type' => 'string', 'example' => 'assigned'],
                                                    'vehicle' => ['type' => 'object'],
                                                    'route' => ['type' => 'object'],
                                                ]
                                            ],
                                            'created_at' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-04-21T09:00:00Z']
                                        ]
                                    ])
                                )
                            ])
                        ),
                        '404' => new Model\Response('No hay próximo viaje')
                    ]
                )
            )
        );

        $openApi->getPaths()->addPath(
            '/api/trips/history',
            new Model\PathItem(
                get: new Model\Operation(
                    operationId: 'getTripHistory',
                    tags: ['Passenger Reservations'],
                    summary: 'Historial de viajes',
                    description: 'Devuelve el historial paginado de viajes del pasajero autenticado.',
                    security: [['bearerAuth' => []]],
                    parameters: [
                        new Model\Parameter(
                            name: 'status',
                            in: 'query',
                            required: false,
                            description: 'Filtrar por estado de la reservación',
                            schema: ['type' => 'string', 'example' => 'cancelled']
                        ),
                        new Model\Parameter(
                            name: 'per_page',
                            in: 'query',
                            required: false,
                            description: 'Cantidad de resultados por página',
                            schema: ['type' => 'integer', 'example' => 15]
                        ),
                        new Model\Parameter(
                            name: 'page',
                            in: 'query',
                            required: false,
                            description: 'Número de página',
                            schema: ['type' => 'integer', 'example' => 1]
                        ),
                    ],
                    responses: [
                        '200' => new Model\Response(
                            description: 'Historial paginado de viajes',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'object',
                                        'properties' => [
                                            'data' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'booking_id' => ['type' => 'integer', 'example' => 10],
                                                        'status' => ['type' => 'string', 'example' => 'cancelled'],
                                                        'seats' => [
                                                            'type' => 'array',
                                                            'items' => [
                                                                'type' => 'object',
                                                                'properties' => [
                                                                    'seat' => ['type' => 'integer', 'example' => 2],
                                                                    'qr' => ['type' => 'string', 'example' => 'uuid-1']
                                                                ]
                                                            ]
                                                        ],
                                                        'trip' => [
                                                            'type' => 'object',
                                                            'properties' => [
                                                                'id' => ['type' => 'integer', 'example' => 1],
                                                                'datetime' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-04-21T10:00:00Z'],
                                                                'status' => ['type' => 'string', 'example' => 'assigned'],
                                                                'vehicle' => ['type' => 'object'],
                                                                'route' => ['type' => 'object'],
                                                            ]
                                                        ],
                                                        'created_at' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-04-21T09:00:00Z']
                                                    ]
                                                ]
                                            ],
                                            'links' => ['type' => 'object'],
                                            'meta' => ['type' => 'object']
                                        ]
                                    ])
                                )
                            ])
                        )
                    ]
                )
            )
        );

        $openApi->getPaths()->addPath(
            '/api/reservations/{id}',
            new Model\PathItem(
                get: new Model\Operation(
                    operationId: 'getReservationDetail',
                    tags: ['Passenger Reservations'],
                    summary: 'Detalle de reservación',
                    description: 'Devuelve el detalle de la reservación del pasajero autenticado.',
                    security: [['bearerAuth' => []]],
                    parameters: [
                        new Model\Parameter(
                            name: 'id',
                            in: 'path',
                            required: true,
                            description: 'ID de la reservación',
                            schema: ['type' => 'integer', 'example' => 10]
                        ),
                    ],
                    responses: [
                        '200' => new Model\Response(
                            description: 'Detalle de la reservación',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'object',
                                        'properties' => [
                                            'booking_id' => ['type' => 'integer', 'example' => 10],
                                            'status' => ['type' => 'string', 'example' => 'active'],
                                            'seats' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'seat' => ['type' => 'integer', 'example' => 2],
                                                        'qr' => ['type' => 'string', 'example' => 'uuid-1']
                                                    ]
                                                ]
                                            ],
                                            'trip' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'id' => ['type' => 'integer', 'example' => 1],
                                                    'datetime' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-04-21T10:00:00Z'],
                                                    'status' => ['type' => 'string', 'example' => 'assigned'],
                                                    'vehicle' => ['type' => 'object'],
                                                    'route' => ['type' => 'object'],
                                                ]
                                            ],
                                            'created_at' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-04-21T09:00:00Z']
                                        ]
                                    ])
                                )
                            ])
                        ),
                        '404' => new Model\Response('Reservación no encontrada')
                    ]
                )
            )
        );

        $openApi->getPaths()->addPath(
            '/api/reservations/{id}/cancel',
            new Model\PathItem(
                patch: new Model\Operation(
                    operationId: 'cancelReservation',
                    tags: ['Passenger Reservations'],
                    summary: 'Cancelar reservación',
                    description: 'Cancela la reservación del pasajero autenticado.',
                    security: [['bearerAuth' => []]],
                    parameters: [
                        new Model\Parameter(
                            name: 'id',
                            in: 'path',
                            required: true,
                            description: 'ID de la reservación',
                            schema: ['type' => 'integer', 'example' => 10]
                        ),
                    ],
                    responses: [
                        '200' => new Model\Response(
                            description: 'Reservación cancelada',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean', 'example' => true],
                                            'message' => ['type' => 'string', 'example' => 'Reservación cancelada']
                                        ]
                                    ])
                                )
                            ])
                        ),
                        '404' => new Model\Response('Reservación no encontrada'),
                        '409' => new Model\Response('La reservación ya está cancelada')
                    ]
                )
            )
        );
        // Endpoint: Viajes recientes del pasajero
        $openApi->getPaths()->addPath(
            '/api/trips/recent',
            new Model\PathItem(
                get: new Model\Operation(
                    operationId: 'getRecentTrips',
                    tags: ['Passenger Reservations'],
                    summary: 'Obtener viajes recientes del pasajero',
                    description: 'Devuelve los últimos N viajes del pasajero autenticado. Permite filtrar por estado y limitar la cantidad.',
                    security: [['bearerAuth' => []]],
                    parameters: [
                        new Model\Parameter(
                            name: 'limit',
                            in: 'query',
                            required: false,
                            description: 'Cantidad máxima de viajes a devolver (default 5)',
                            schema: ['type' => 'integer', 'example' => 5]
                        ),
                        new Model\Parameter(
                            name: 'status',
                            in: 'query',
                            required: false,
                            description: 'Filtrar por estado de la reservación',
                            schema: ['type' => 'string', 'example' => 'active']
                        ),
                    ],
                    responses: [
                        '200' => new Model\Response(
                            description: 'Lista de viajes recientes',
                            content: new \ArrayObject([
                                'application/json' => new Model\MediaType(
                                    schema: new \ArrayObject([
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'booking_id' => ['type' => 'integer', 'example' => 10],
                                                'status' => ['type' => 'string', 'example' => 'active'],
                                                'seats' => [
                                                    'type' => 'array',
                                                    'items' => [
                                                        'type' => 'object',
                                                        'properties' => [
                                                            'seat' => ['type' => 'integer', 'example' => 2],
                                                            'qr' => ['type' => 'string', 'example' => 'uuid-1']
                                                        ]
                                                    ]
                                                ],
                                                'trip' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'id' => ['type' => 'integer', 'example' => 1],
                                                        'datetime' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-04-21T10:00:00Z'],
                                                        'status' => ['type' => 'string', 'example' => 'assigned'],
                                                        'vehicle' => ['type' => 'object'],
                                                        'route' => ['type' => 'object'],
                                                    ]
                                                ],
                                                'created_at' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-04-21T09:00:00Z']
                                            ]
                                        ]
                                    ])
                                )
                            ])
                        )
                    ]
                )
            )
        );
        return $openApi;
    }

    private function registerSecuritySchemes(OpenApi $openApi): OpenApi
    {
        $components = $openApi->getComponents();
        $securitySchemes = $components->getSecuritySchemes() ?? new \ArrayObject();

        $securitySchemes['bearerAuth'] = new Model\SecurityScheme(
            type: 'http',
            description: 'Token de cliente con Sanctum. Enviar: Bearer {token}',
            scheme: 'bearer',
            bearerFormat: 'Token'
        );

        return $openApi->withComponents($components->withSecuritySchemes($securitySchemes));
    }
}
