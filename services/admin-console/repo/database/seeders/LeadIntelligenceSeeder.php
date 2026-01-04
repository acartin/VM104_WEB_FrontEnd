<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lead;
use App\Models\Conversation;
use App\Models\Client;
use App\Models\LeadSource;
use App\Models\ContactPreference;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeadIntelligenceSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::first();
        if (!$client) {
            $this->command->error('No client found. Please seed clients first.');
            return;
        }

        /**
         * SCORING RANGES PER docs/SCORING_SYSTEM.md:
         * 1. Engagement: 0-30
         * 2. Finance: 0-30
         * 3. Timeline: 0-20
         * 4. Match: 0-15
         * 5. Info: 0-5
         * TOTAL: 100
         */

        $scenarios = [
            [
                'name' => 'Carlos Méndez',
                'email' => 'carlos.mendez@example.com',
                'phone' => '+1-555-0101',
                'income' => 150000,
                'debts' => 2000,
                'source_id' => 3, // Facebook Ads
                'utm' => [
                    'source' => 'facebook',
                    'medium' => 'paid',
                    'campaign' => 'luxury-homes-2026',
                    'content' => 'video-penthouse',
                ],
                'property' => [
                    'title' => 'Penthouse Azure Sky',
                    'ref' => 'wp-lux-01',
                    'url' => 'https://luxury.com/penthouse-azure',
                    'value' => 850000,
                    'beds' => 4,
                    'baths' => 3,
                    'area' => 280,
                ],
                'scores' => ['engagement' => 30, 'finance' => 30, 'timeline' => 20, 'match' => 15, 'info' => 5], // Total 100
                'sentiment' => 'positive',
                'summary' => 'Comprador de alto perfil interesado en propiedades de lujo. Presupuesto excedente y urgencia por mudanza internacional en 3 meses.',
                'chat' => [
                    ['sender' => 'bot', 'text' => '¡Hola Carlos! Bienvenido a Luxury Homes. ¿En qué puedo ayudarte?'],
                    ['sender' => 'lead', 'text' => 'Hola, vi el video del penthouse en Facebook. ¿Sigue disponible?'],
                    ['sender' => 'bot', 'text' => 'Sí, Carlos. El Penthouse Azure sigue disponible. ¿Te gustaría conocer el precio y las facilidades?'],
                    ['sender' => 'lead', 'text' => 'Sí, por favor. Tengo un presupuesto de hasta $900k y busco cerrar rápido.'],
                    ['sender' => 'bot', 'text' => 'Excelente. El precio es de $850k. ¿Te parece bien si agendamos una visita virtual hoy?'],
                    ['sender' => 'lead', 'text' => 'Mejor presencial, estaré en la ciudad este viernes.'],
                ]
            ],
            [
                'name' => 'Elena Rodríguez',
                'email' => 'elena.rodriguez@example.com',
                'phone' => '+1-555-0102',
                'income' => 65000,
                'debts' => 15000,
                'source_id' => 2, // Google SEM
                'utm' => [
                    'source' => 'google',
                    'medium' => 'cpc',
                    'campaign' => 'first-time-buyers',
                    'content' => 'search-ad-low-apr',
                ],
                'property' => [
                    'title' => 'Family Home Heritage',
                    'ref' => 'wp-fam-02',
                    'url' => 'https://homes.com/family-heritage',
                    'value' => 210000,
                    'beds' => 3,
                    'baths' => 2,
                    'area' => 140,
                ],
                'scores' => ['engagement' => 15, 'finance' => 10, 'timeline' => 18, 'match' => 12, 'info' => 3],
                'sentiment' => 'neutral',
                'summary' => 'Primera compra. Mucha urgencia por dejar de pagar alquiler pero capacidad financiera limitada por deudas actuales.',
                'chat' => [
                    ['sender' => 'bot', 'text' => '¡Hola Elena! ¿Buscas tu primera casa?'],
                    ['sender' => 'lead', 'text' => 'Hola, sí. Pago mucho de renta y ya quiero mi propia casa.'],
                    ['sender' => 'bot', 'text' => 'Te entiendo. ¿Cuál es tu presupuesto mensual para una hipoteca?'],
                    ['sender' => 'lead', 'text' => 'No más de $1,200 mensuales. Tengo algunas deudas pero gano bien.'],
                ]
            ],
            [
                'name' => 'John Smith (Investor)',
                'email' => 'john.invest@global.com',
                'phone' => '+1-555-9000',
                'income' => 450000,
                'debts' => 0,
                'source_id' => 15, // Zillow
                'utm' => [
                    'source' => 'zillow',
                    'medium' => 'portal',
                    'campaign' => 'investment-units',
                ],
                'property' => [
                    'title' => 'Condo Block B-12',
                    'ref' => 'wp-inv-99',
                    'url' => 'https://portals.com/condo-b12',
                    'value' => 150000,
                    'beds' => 1,
                    'baths' => 1,
                    'area' => 65,
                ],
                'scores' => ['engagement' => 22, 'finance' => 30, 'timeline' => 10, 'match' => 8, 'info' => 4],
                'sentiment' => 'positive',
                'summary' => 'Inversionista recurrente buscando unidades pequeñas para ROI de alquiler. No tiene prisa pero paga en efectivo.',
                'chat' => [
                    ['sender' => 'bot', 'text' => 'Hello John! Found a good unit today.'],
                    ['sender' => 'lead', 'text' => 'Hi. Send me the ROI projection for the Condo Block B.'],
                    ['sender' => 'bot', 'text' => 'Sure, it is roughly 7.2% net. Cash price is $150k.'],
                    ['sender' => 'lead', 'text' => 'Interested. Keep me posted if price drops to $140k.'],
                ]
            ],
            [
                'name' => 'Lucía Ferraro',
                'email' => 'lucia.f@social.net',
                'phone' => '+1-555-7788',
                'income' => 35000,
                'debts' => 2000,
                'source_id' => 4, // Instagram Ads
                'utm' => [
                    'source' => 'instagram',
                    'medium' => 'social',
                    'campaign' => 'aesthetic-apartments',
                ],
                'property' => [
                    'title' => 'Studio Moderno Centro',
                    'ref' => 'wp-stu-05',
                    'url' => 'https://chic.com/studio-moderno',
                    'value' => 120000,
                    'beds' => 1,
                    'baths' => 1,
                    'area' => 45,
                ],
                'scores' => ['engagement' => 29, 'finance' => 5, 'timeline' => 5, 'match' => 13, 'info' => 2],
                'sentiment' => 'positive',
                'summary' => 'Lead muy joven, muy entusiasmada con el diseño pero sin ahorros suficientes para el pago inicial aún.',
                'chat' => [
                    ['sender' => 'bot', 'text' => '¡Hola Lucía! Amamos este estudio tanto como tú.'],
                    ['sender' => 'lead', 'text' => '¡Es precioso! El diseño es justo lo que buscaba.'],
                    ['sender' => 'bot', 'text' => '¿Te gustaría ir a verlo mañana?'],
                    ['sender' => 'lead', 'text' => 'Me encantaría pero todavía me falta ahorrar un poco para pedir el crédito.'],
                ]
            ],
            [
                'name' => 'Roberto "Tito" Gomez',
                'email' => 'tito.gomez@local.com',
                'phone' => '+1-555-4433',
                'income' => 95000,
                'debts' => 8000,
                'source_id' => 7, // QR Code
                'utm' => [
                    'source' => 'direct',
                    'medium' => 'offline',
                    'campaign' => 'qr-sign-main-st',
                ],
                'property' => [
                    'title' => 'Townhouse Park View',
                    'ref' => 'wp-tow-10',
                    'url' => 'https://local.com/parkview',
                    'value' => 380000,
                    'beds' => 3,
                    'baths' => 2.5,
                    'area' => 180,
                ],
                'scores' => ['engagement' => 20, 'finance' => 25, 'timeline' => 16, 'match' => 14, 'info' => 3],
                'sentiment' => 'neutral',
                'summary' => 'Vecino de la zona interesado por ver el letrero. Tiene casa propia y quiere subir de nivel (upgrade).',
                'chat' => [
                    ['sender' => 'bot', 'text' => '¡Hola Roberto! Gracias por escanear nuestro código.'],
                    ['sender' => 'lead', 'text' => 'Vivo a dos cuadras. Siempre paso por aquí y me dio curiosidad el precio.'],
                    ['sender' => 'bot', 'text' => 'Esta maravilla está en $380,000. ¿Venderías tu casa actual?'],
                    ['sender' => 'lead', 'text' => 'Probablemente sí. Es más pequeña que esta.'],
                ]
            ],
            [
                'name' => 'Andrés Villalobos',
                'email' => 'villalobos_a@corp.com',
                'phone' => '+1-555-5555',
                'income' => 180000,
                'debts' => 1000,
                'source_id' => 1, // Google SEO
                'utm' => [
                    'source' => 'google',
                    'medium' => 'organic',
                    'campaign' => 'seo-best-neighborhoods',
                ],
                'property' => [
                    'title' => 'Estate Mansion Hills',
                    'ref' => 'wp-man-01',
                    'url' => 'https://estate.com/mansion-hills',
                    'value' => 1500000,
                    'beds' => 6,
                    'baths' => 5,
                    'area' => 600,
                ],
                'scores' => ['engagement' => 12, 'finance' => 30, 'timeline' => 8, 'match' => 10, 'info' => 5],
                'sentiment' => 'neutral',
                'summary' => 'Investigación técnica profunda. Busca datos de zonificación y plusvalía histórica antes de considerar visita.',
                'chat' => [
                    ['sender' => 'bot', 'text' => 'Hola Andrés. Veo que revisaste el informe de plusvalía.'],
                    ['sender' => 'lead', 'text' => 'Correcto. Necesito saber los planes de desarrollo urbano para esa zona en los próximos 10 años.'],
                    ['sender' => 'bot', 'text' => 'Tengo los documentos. ¿Prefieres una llamada para explicártelos?'],
                    ['sender' => 'lead', 'text' => 'Por ahora envíamelos por correo. Si la proyección es buena avanzamos.'],
                ]
            ],
            [
                'name' => 'Sara Cohen',
                'email' => 'sara.cohen@example.com',
                'phone' => '+1-555-1212',
                'income' => 110000,
                'debts' => 4000,
                'source_id' => 10, // Website
                'utm' => [
                    'source' => 'website',
                    'medium' => 'direct',
                    'campaign' => 'contact-form-main',
                ],
                'property' => [
                    'title' => 'Garden Loft',
                    'ref' => 'wp-loft-12',
                    'url' => 'https://homes.com/garden-loft',
                    'value' => 290000,
                    'beds' => 2,
                    'baths' => 2,
                    'area' => 110,
                ],
                'scores' => ['engagement' => 27, 'finance' => 28, 'timeline' => 20, 'match' => 15, 'info' => 5], // Total 95
                'sentiment' => 'positive',
                'summary' => 'Buscando reubicación inmediata por nuevo trabajo. Calificada al 100%. Prioridad máxima para comercial.',
                'chat' => [
                    ['sender' => 'bot', 'text' => '¡Hola Sara! Vimos tu mensaje en la web.'],
                    ['sender' => 'lead', 'text' => 'Urgente. Empiezo a trabajar el día 15 y necesito tener donde vivir ya.'],
                    ['sender' => 'bot', 'text' => 'Tenemos el Garden Loft listo para entrega inmediata.'],
                    ['sender' => 'lead', 'text' => 'Perfecto. Mandame la lista de requisitos para el cierre.'],
                ]
            ],
            [
                'name' => 'Miguel Sanz',
                'email' => 'miguelsanz@trash.com',
                'phone' => '+1-555-0001',
                'income' => 20000,
                'debts' => 30000,
                'source_id' => 11, // Facebook
                'utm' => [
                    'source' => 'facebook',
                    'medium' => 'cold',
                    'campaign' => 'giveaway-promo',
                ],
                'property' => [
                    'title' => 'Basic Condo Unit',
                    'ref' => 'wp-bas-01',
                    'url' => 'https://cheap.com/basic-condo',
                    'value' => 85000,
                    'beds' => 1,
                    'baths' => 1,
                    'area' => 40,
                ],
                'scores' => ['engagement' => 5, 'finance' => 2, 'timeline' => 4, 'match' => 3, 'info' => 1],
                'sentiment' => 'negative',
                'summary' => 'Lead de baja calidad atraído por promoción de sorteo. No tiene intención real de compra ni capacidad financiera.',
                'chat' => [
                    ['sender' => 'bot', 'text' => 'Hola Miguel. ¿Te interesa el sorteo o la propiedad?'],
                    ['sender' => 'lead', 'text' => 'Solo quiero saber si gané el premio.'],
                    ['sender' => 'bot', 'text' => 'Estamos hablando de la casa de $85k.'],
                    ['sender' => 'lead', 'text' => 'No tengo dinero para eso.'],
                ]
            ],
            [
                'name' => 'Patricia "Pati" Luna',
                'email' => 'pati.luna@referral.com',
                'phone' => '+1-555-8899',
                'income' => 135000,
                'debts' => 5000,
                'source_id' => 13, // Referral
                'utm' => [
                    'source' => 'referral',
                    'medium' => 'direct',
                    'campaign' => 'friend-program',
                ],
                'property' => [
                    'title' => 'Lake View Villa',
                    'ref' => 'wp-vil-08',
                    'url' => 'https://luxury.com/lake-view',
                    'value' => 550000,
                    'beds' => 4,
                    'baths' => 4,
                    'area' => 320,
                ],
                'scores' => ['engagement' => 30, 'finance' => 29, 'timeline' => 15, 'match' => 12, 'info' => 5],
                'sentiment' => 'positive',
                'summary' => 'Referida por un cliente actual. Muy interesada y con mucha confianza en la agencia. Buscando casa de retiro.',
                'chat' => [
                    ['sender' => 'bot', 'text' => '¡Hola Patricia! Un gusto saludarte.'],
                    ['sender' => 'lead', 'text' => 'Hola. Mi amiga María me compró con ustedes y me recomendó mucho la villa del lago.'],
                    ['sender' => 'bot', 'text' => 'María es una gran cliente. Esa villa es espectacular.'],
                    ['sender' => 'lead', 'text' => 'Me gustaría retirarme ahí el próximo año. ¿Qué me recomiendan?'],
                ]
            ],
            [
                'name' => 'Fernando Ruiz',
                'email' => 'fruiz@tech.io',
                'phone' => '+1-555-2233',
                'income' => 105000,
                'debts' => 12000,
                'source_id' => 1, // Google SEO
                'utm' => [
                    'source' => 'google',
                    'medium' => 'organic',
                    'campaign' => 'it-professionals-mortgage',
                ],
                'property' => [
                    'title' => 'Smart Home Loft',
                    'ref' => 'wp-sma-07',
                    'url' => 'https://techy.com/smart-loft',
                    'value' => 320000,
                    'beds' => 2,
                    'baths' => 2,
                    'area' => 100,
                ],
                'scores' => ['engagement' => 25, 'finance' => 25, 'timeline' => 19, 'match' => 14, 'info' => 4],
                'sentiment' => 'positive',
                'summary' => 'Tech profile. Valora mucho la automatización y conectividad. Financiamiento pre-aprobado listo.',
                'chat' => [
                    ['sender' => 'bot', 'text' => '¡Hola Fernando! ¿Buscando una casa inteligente?'],
                    ['sender' => 'lead', 'text' => 'Sí, que tenga buena fibra óptica y domótica integrada.'],
                    ['sender' => 'bot', 'text' => 'El loft tiene sistema Control4 y 1Gb de internet simétrico.'],
                    ['sender' => 'lead', 'text' => 'Interesante. Ya tengo el crédito pre-aprobado por el banco.'],
                ]
            ],
        ];

        $preferenceIds = ContactPreference::pluck('id', 'slug')->toArray();
        $preferenceSlugs = array_keys($preferenceIds);
        $newStatusId = DB::table('lead_statuses')->where('key', 'new')->value('id');

        foreach ($scenarios as $s) {
            // Eliminar lead si ya existe para evitar duplicados en pruebas
            Lead::where('email', $s['email'])->forceDelete();

            $lead = Lead::create([
                'client_id' => $client->id,
                'source_id' => $s['source_id'],
                'status_id' => $newStatusId,
                'contact_preference_id' => $preferenceIds[$preferenceSlugs[array_rand($preferenceSlugs)]] ?? null,
                'full_name' => $s['name'],
                'email' => $s['email'],
                'phone' => $s['phone'],
                'declared_income' => $s['income'],
                'current_debts' => $s['debts'],
                'financial_currency_id' => 'USD',
                
                // Scores
                'score_engagement' => $s['scores']['engagement'],
                'score_finance' => $s['scores']['finance'],
                'score_timeline' => $s['scores']['timeline'],
                'score_match' => $s['scores']['match'],
                'score_info' => $s['scores']['info'],
                
                // Property tracking
                'source_property_ref' => $s['property']['ref'],
                'source_property_url' => $s['property']['url'],
                'estimated_value' => $s['property']['value'],
                'property_snapshot' => [
                    'title' => $s['property']['title'],
                    'bedrooms' => $s['property']['beds'],
                    'bathrooms' => $s['property']['baths'],
                    'area_sqm' => $s['property']['area'],
                ],
                
                // UTM tracking
                'utm_source' => $s['utm']['source'] ?? null,
                'utm_medium' => $s['utm']['medium'] ?? null,
                'utm_campaign' => $s['utm']['campaign'] ?? null,
                'utm_content' => $s['utm']['content'] ?? null,
                
                // Session metadata
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'ip_address' => '192.168.1.' . rand(1, 254),
            ]);

            // Crear conversación simulada
            Conversation::create([
                'lead_id' => $lead->id,
                'platform' => 'webchat',
                'conversation_id' => 'sim_' . Str::random(10),
                'messages' => array_map(function($m) {
                    return array_merge($m, ['timestamp' => now()->subMinutes(rand(5, 60))->toIso8601String(), 'type' => 'text']);
                }, $s['chat']),
                'summary' => $s['summary'],
                'sentiment' => $s['sentiment'],
                'started_at' => now()->subHours(2),
                'ended_at' => now()->subHours(1),
                'last_message_at' => now()->subMinutes(rand(1, 30)),
                'total_messages' => count($s['chat']),
                'bot_messages' => count(array_filter($s['chat'], fn($m) => $m['sender'] === 'bot')),
                'lead_messages' => count(array_filter($s['chat'], fn($m) => $m['sender'] === 'lead')),
            ]);
        }
        
        $this->command->info('10 Intelligence Leads and Conversations seeded successfully with CORRECT ranges!');
    }
}
