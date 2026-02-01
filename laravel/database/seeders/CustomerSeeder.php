<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                Customer::name => 'Max Mustermann',
                Customer::company_name => 'Mustermann GmbH',
                Customer::email => 'max.mustermann@mustermann-gmbh.de',
                Customer::phone => '+49 30 12345678',
                Customer::address => 'Musterstraße 123, 10115 Berlin',
                Customer::payment_method => 'Überweisung',
                Customer::notes => 'Langjähriger Kunde, zahlt immer pünktlich.',
            ],
            [
                Customer::name => 'Anna Schmidt',
                Customer::company_name => 'Schmidt Sport Marketing',
                Customer::email => 'anna.schmidt@schmidt-marketing.de',
                Customer::phone => '+49 40 98765432',
                Customer::address => 'Alsterweg 45, 20099 Hamburg',
                Customer::payment_method => 'Lastschrift',
                Customer::notes => 'Interessiert an Premium-Positionen.',
            ],
            [
                Customer::name => 'Thomas Müller',
                Customer::company_name => 'Müller & Söhne AG',
                Customer::email => 'thomas.mueller@mueller-soehne.de',
                Customer::phone => '+49 89 55544433',
                Customer::address => 'Maximilianstraße 78, 80539 München',
                Customer::payment_method => 'Überweisung',
                Customer::notes => 'VIP-Kunde, bevorzugt Stadion-Positionen.',
            ],
            [
                Customer::name => 'Sarah Weber',
                Customer::company_name => 'Weber Sportswear',
                Customer::email => 'sarah.weber@weber-sportswear.de',
                Customer::phone => '+49 211 77788899',
                Customer::address => 'Königsallee 100, 40212 Düsseldorf',
                Customer::payment_method => 'Kreditkarte',
                Customer::notes => 'Neue Kundin, sehr engagiert.',
            ],
            [
                Customer::name => 'Michael Becker',
                Customer::company_name => null,
                Customer::email => 'michael.becker@email.de',
                Customer::phone => '+49 351 22233344',
                Customer::address => 'Prager Straße 20, 01069 Dresden',
                Customer::payment_method => 'Überweisung',
                Customer::notes => 'Privatperson, kleines Budget.',
            ],
            [
                Customer::name => 'Julia Fischer',
                Customer::company_name => 'Fischer Consulting',
                Customer::email => 'julia.fischer@fischer-consulting.de',
                Customer::phone => '+49 30 66677788',
                Customer::address => 'Friedrichstraße 200, 10117 Berlin',
                Customer::payment_method => 'Lastschrift',
                Customer::notes => 'Mehrere Felder gebucht.',
            ],
            [
                Customer::name => 'Peter Hoffmann',
                Customer::company_name => 'Hoffmann Automobil GmbH',
                Customer::email => 'p.hoffmann@hoffmann-auto.de',
                Customer::phone => '+49 40 33344455',
                Customer::address => 'Hafenstraße 67, 20359 Hamburg',
                Customer::payment_method => 'Überweisung',
                Customer::notes => 'Möchte langfristige Partnerschaft.',
            ],
            [
                Customer::name => 'Lisa Schneider',
                Customer::company_name => 'Schneider Fitness Center',
                Customer::email => 'lisa.schneider@schneider-fitness.de',
                Customer::phone => '+49 89 11122233',
                Customer::address => 'Sendlinger Straße 45, 80331 München',
                Customer::payment_method => 'Lastschrift',
                Customer::notes => 'Interessiert an Trainingsfeldern.',
            ],
            [
                Customer::name => 'Daniel Koch',
                Customer::company_name => 'Koch Immobilien',
                Customer::email => 'daniel.koch@koch-immobilien.de',
                Customer::phone => '+49 211 99988877',
                Customer::address => 'Schadowstraße 88, 40212 Düsseldorf',
                Customer::payment_method => 'Überweisung',
                Customer::notes => 'Bevorzugt zentrale Positionen.',
            ],
            [
                Customer::name => 'Sabine Wagner',
                Customer::company_name => 'Wagner Events',
                Customer::email => 'sabine.wagner@wagner-events.de',
                Customer::phone => '+49 351 44455566',
                Customer::address => 'Altmarkt 15, 01067 Dresden',
                Customer::payment_method => 'Kreditkarte',
                Customer::notes => 'Organisiert Sportveranstaltungen.',
            ],
            [
                Customer::name => 'Frank Zimmermann',
                Customer::company_name => 'Zimmermann Tech Solutions',
                Customer::email => 'frank.zimmermann@ztech.de',
                Customer::phone => '+49 30 77766655',
                Customer::address => 'Alexanderplatz 1, 10178 Berlin',
                Customer::payment_method => 'Überweisung',
                Customer::notes => 'Tech-Firma, sucht moderne Werbeflächen.',
            ],
            [
                Customer::name => 'Claudia Meyer',
                Customer::company_name => null,
                Customer::email => 'claudia.meyer@email.de',
                Customer::phone => '+49 40 55566677',
                Customer::address => 'Reeperbahn 150, 20359 Hamburg',
                Customer::payment_method => 'Lastschrift',
                Customer::notes => 'Stammkundin seit 2 Jahren.',
            ],
        ];

        foreach ($customers as $customerData) {
            Customer::create($customerData);
        }
    }
}
