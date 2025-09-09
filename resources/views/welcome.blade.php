<x-layouts.app>

    <x-hero />

    <x-feature-stripe />

    <section id="quienes-somos">
        <x-split-info image="img/fondos/quienes-somos.png" imageAlt="Profesionales en capacitación" imagePosition="left"
            color="#47A8DF" objectPos="[40%_50%]" icon="img/icons/hand-plus.png" iconClass="h-16 md:h-20 lg:h-26 w-auto"
            titleHtml="¿Quiénes <br> somos?"
            :textHtml="'Somos profesionales del área de la salud donde nos enfocamos en brindar capacitaciones de alta calidad para ayudar a nuestros clientes a alcanzar sus objetivos. Nuestro equipo de expertos está comprometido con la excelencia y la innovación, y nos esforzamos por crear entornos de aprendizaje dinámicos y efectivos.'"
            buttonLabel="Ver cursos" buttonHref="#" />

        <x-split-info image="img/fondos/mision.png" imageAlt="Auditorio y conferencia" imagePosition="right"
            color="#19355C" objectPos="[60%_60%]" icon="img/icons/docentes.png" iconClass="h-16 md:h-22 lg:h-28 w-auto"
            titleHtml="Nuestra Misión"
            :textHtml="'Ofrecemos programas de capacitación de alta calidad y accesibilidad, con docentes nacionales e internacionales, que desarrollan habilidades laborales y promueven el crecimiento profesional y social de la región, mediante un enfoque transdisciplinario.<br><br>Nuestra misión es empoderar a personas y organizaciones para que alcancen su máximo potencial.'"
            buttonLabel="Ver cursos" buttonHref="#" />

        <x-split-info image="img/fondos/capacitate.png" imageAlt="Profesional usando tablet" imagePosition="left"
            color="#E71F6C" objectPos="22% 50%" icon="img/icons/brain.png" iconClass="h-18 md:h-24 lg:h-32 w-auto"
            titleHtml="Capacítate hoy con profesionales de alto nivel nacional e internacional."
            :textHtml="'Líder en capacitación y desarrollo de habilidades para el profesional de salud, educación y empresarial, reconocida por su excelencia en docentes.'"
            :showButton="false" />
    </section>

    <x-courses-preview />

    <x-certificaciones-strip />
    <x-support-strip />
    <x-testimonials />
    <livewire:contact-form />

</x-layouts.app>
