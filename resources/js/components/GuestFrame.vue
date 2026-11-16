<template>
    <div class="guest">
        <section class="guest-story">
            <div class="logo" style="padding: 0;">
                <b>hd</b>
                <span>helpdesk</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 28px;">
                <h1>Każde zgłoszenie ma numer, opiekuna i czas odpowiedzi.</h1>
                <div class="guest-cards">
                    <div class="ticket-card high" style="cursor: default;">
                        <div class="top"><span># TCK-000004</span><span class="tag">Wysoki</span></div>
                        <h3>VPN się rozłącza</h3>
                        <p>Po zmianie sieci tunel nie wraca.</p>
                    </div>
                    <div class="ticket-card urgent" style="cursor: default;">
                        <div class="top"><span># TCK-000002</span><span class="tag">Pilny</span></div>
                        <h3>Brak dostępu do poczty</h3>
                        <p>Odpowiedź w 8 minut.</p>
                    </div>
                </div>
            </div>

            <p style="color: #b9bac0; margin: 0;">Klient opisuje problem. Zespół prowadzi rozmowę do rozwiązania.</p>
        </section>

        <section class="guest-form">
            <div style="width: min(380px, 100%);">
                <slot />
            </div>
        </section>
    </div>
</template>
