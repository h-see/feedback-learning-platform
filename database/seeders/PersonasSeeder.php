<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Persona\Models\Persona;
use App\Persona\Models\CounsellingField;

class PersonasSeeder extends Seeder
{
    /**
     * Personas definition
     */
    private function personas()
    {
        return [
            [   'name' => 'Elke',
                'counsellingField' => 'Suchtberatung',
                'properties' => [
                    'Steckbrief' => [
                        'Alter' => 37,
                        'Familienstand' => 'verheiratet, mehrere Kinder',
                        'Geschlecht' => 'weiblich',
                        'Job' => 'Hausfrau'
                    ],
                    'Hauptanliegen' => "Elke macht sich Sorgen um ihren Sohn. Sie vermutet, dass er aufgrund seines Freundeskreises Drogen konsumiert bzw. Marihuana raucht. Er war grundsätzlich kein schlechter Schüler, nur leider haben sich seine Noten in letzter Zeit verschlechtert. Sie hat bereits versucht mit Max darüber zu reden, sowohl über seine Schulleistungen, als auch über den Konsum, er blockt hier immer wieder ab und will nicht darüber reden. Elke vermutet, dass das veränderte Verhalten des Sohnes auf seinen Freundeskreis zurückzuführen ist, da diese einen schlechten Einfluss auf ihn haben.",
                    'Nebenanliegen' => [
                        'Gefährdung der Beziehung zum Sohn',
                        'Elke macht sich Vorwürfe',
                        'Sorgen um die Zukunft des Sohnes',
                        'Allein gelassen bei Streitgesprächen (von den anderen Familienmitgliedern)',
                        'Mann arbeitet viel und ist kaum präsent'
                    ],
                    'Sprachliche Merkmale' => [
                        'Anrede: Sie',
                        'Keine Verwendung von Abkürzungen und Umgangssprache',
                        'Keine Verwendung von Chatzeichen zur Darstellung von Emotionen',
                        'Kurze prägnante Rückmeldungen',
                        'Viele "..." zwischen den Sätzen',
                        'Wörter wie beispielsweise "Kiffen" werden verwendet'
                    ]
                ]
            ],
            [   'name' => 'Jessica Bergmann',
                'counsellingField' => 'Suchtberatung',
                'properties' => [
                    'Steckbrief' => [
                        'Alter' => 17,
                        'Familienstand' => 'ledig',
                        'Geschlecht' => 'weiblich',
                        'Job' => 'Schülerin'
                    ],
                    'Hauptanliegen' => "Jessica ist sich unsicher. Sie geht regelmäßig mit Freund*innen aus und trinkt dementsprechend regelmäßig Alkohol. Sie beschreibt aber auch, dass sie manchmal trinkt, um sich abzulenken. Sie beschreibt, dass gerade bei schlechten Gefühlen der Gedanke an Alkohol sich gut anfühlt. Sie ist sich daher unsicher, ob sie eine Alkoholsucht entwickelt und möchte sich diesbezüglich informieren und beraten lassen.",
                    'Nebenanliegen' => [
                        'Unsicherheiten in Bezug auf die Zukunft',
                        'Druck seitens der Eltern resultiert in Schulstress'
                    ],
                    'Sprachliche Merkmale' => [
                        'Anrede: Sie',
                        'Keine Verwendung von Abkürzungen und Umgangssprache',
                        'Keine Verwendung von Chatzeichen zur Darstellung von Emotionen',
                        'Kurze prägnante Rückmeldungen',
                        'Viele "..." zwischen den Sätzen',
                        'Wörter wie beispielsweise "Kiffen" werden verwendet'
                    ]
                ]
            ],
            [   'name' => 'Lina',
                'counsellingField' => 'Suchtberatung',
                'properties' => [
                    'Steckbrief' => [
                        'Alter' => 26,
                        'Familienstand' => 'verheiratet',
                        'Geschlecht' => 'weiblich',
                        'Job' => 'unbekannt'
                    ],
                    'Hauptanliegen' => "Lina befindet sich derzeit im Entzug. Sie hat lange Zeit mit ihrem Mann regelmäßig Cannabis konsumiert. Seit einer Woche ist sie nun clean. Davor hat sie in etwa 7 Joints pro Tag geraucht. Problematisch ist hierbei, dass sie gerne aufhören möchte und auch clean bleiben will, aber ihr Mann weiterhin konsumiert. Dies bereitet ihr neben den Entzugserscheinungen (Reizbarkeit, Magen-Darm-Probleme) Schwierigkeiten. Sie weiß einfach nicht, wie sie diese Thematik ansprechen soll, ihr Mann scheint keine Notwendigkeit dahinter zu sehen aufzuhören. Sowohl das Thema mit ihrem Mann, als auch das clean werden hatte sie in anderen Beratungsstellen angesprochen – ohne Erfolg. Sie kann sich vorstellen, dass die letzte Konsequenz die Trennung von ihm wäre, aber sie möchte dies auf jeden Fall verhindern. Die Trennung als letzten Ausweg zu sehen macht ihr selbst nämlich Angst.",
                    'Nebenanliegen' => [
                        'Umgang mit dem Entzug/Entzugserscheinungen',
                        'Suchtdruck lindern',
                        'Angst vor Streitigkeiten'
                    ],
                    'Sprachliche Merkmale' => [
                        'Anrede: Sie',
                        'Keine Abkürzungen',
                        'Verwendung von durchschnittlicher Sprache',
                        'Keine Verwendung von Chatzeichen zur Darstellung von Emotionen',
                        'Kurze prägnante Rückmeldungen',
                        'Teilweise genervte, nicht aussagekräftige Antworten',
                        'Geht manchmal nicht auf Fragen ein',
                        'Starkes Missvertrauen',
                        'Sehr zurückhaltend'
                    ]
                ]
            ],
            [   'name' => 'Luisa',
                'counsellingField' => 'Familienberatung',
                'properties' => [
                    'Steckbrief' => [
                        'Alter' => 12,
                        'Familienstand' => 'ledig',
                        'Geschlecht' => 'weiblich',
                        'Job' => 'Schülerin, 6. Klasse'
                    ],
                    'Hauptanliegen' => "Luisa ist genervt von den Streitereien ihrer Eltern. Immer wenn sie hört, dass die beiden streiten, geht sie in ihr Zimmer und setzt Kopfhörer auf. Durch die ganzen Streitereien befürchtet sie, dass sie sich nicht mehr lieben. Am liebsten würde sie ihre Eltern darauf ansprechen, aber bisher hat sie sich nicht getraut, weil sie Angst hat, dass ihre Gefühle von ihren Eltern nicht ernst genommen werden. Vielleicht kann sie gemeinsam mit ihrer Schwester das Gespräch mit ihren Eltern suchen?",
                    'Nebenanliegen' => [
                        'Sorgen um die Zukunft der Eltern',
                        'Luisa hat Angst, Schuld für weitere Streitereien zu sein, wenn sie ihre Eltern konfrontiert',
                        'Sie befürchtet, dass ihre Eltern ihre Gefühle nicht ernst nehmen könnten'
                    ],
                    'Sprachliche Merkmale' => [
                        'Anrede: Du',
                        'Verwendung von Jugendsprache und Abkürzungen',
                        'Verwendung von Chatzeichen zur Darstellung von Emotionen',
                        'Eher kurze prägnante Rückmeldungen'
                    ]
                ]
            ],
        ];
    }
    
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('personas')->truncate();
    
        foreach ($this->personas() as $personaData) {
            $counsellingField = CounsellingField::where('name', $personaData['counsellingField'])->first();
    
            if ($counsellingField) {
                Persona::create([
                    'name' => $personaData['name'],
                    'counselling_field_id' => $counsellingField->id,
                    'properties' => $personaData['properties']
                ]);
            } else {
                $this->command->error('Counselling field for persona not found');
            }
        }
    
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
