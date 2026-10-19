
<x-app-layout>
    <div class="about-page">
        <section class="about-hero">
            <div class="about-content">

                <h1>
                    Building ideas into
                    <span>solutions.</span>
                </h1>

                <p class="about-intro">
                    Hi, I'm Teodor Trajchev, a Software Engineering student
                    at FINKI with an interest in building practical,
                    user-friendly software solutions.
                </p>

                <p class="about-description">
                    I enjoy turning ideas into working applications,
                    exploring new technologies, and improving my skills
                    through hands-on projects. TaskForge is one of the
                    projects through which I continue to develop my
                    experience in web development, application design,
                    and deployment.
                </p>

                <div class="about-actions">
                    <a
                        href="https://github.com/teodortrajchev"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="about-button about-button-primary"
                    >
                        <i class="fab fa-github"></i>
                        GitHub Profile
                    </a>
                    <a
                        href="https://www.linkedin.com/in/teodor-trajchev-37647a358/?isSelfProfile=true"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="about-button about-button-primary"
                    >
                        <i class="fab fa-github"></i>
                        LinkedIn Profile
                    </a>


                    <a
                        href="https://github.com/teodortrajchev/TaskForge"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="about-button about-button-secondary"
                    >
                        View TaskForge
                        <i class="fas fa-arrow-up-right-from-square"></i>
                    </a>
                </div>
            </div>

            <div class="about-profile">
                <div class="profile-glow"></div>

                <div class="profile-card">
                    <div class="profile-avatar">
                        TT
                    </div>

                    <h2>Teodor Trajchev</h2>
                    <p>Software Engineering Student</p>
                    <div class="profile-divider"></div>
                </div>
            </div>
        </section>

        <section class="about-bottom">
            <div>
                <span class="about-label">MY APPROACH</span>
                <h2>Learn. Build. Improve.</h2>
            </div>

            <p>
                I believe the best way to grow as a developer is to build
                real projects, solve problems, and keep learning along
                the way. Each project is an opportunity to turn new
                knowledge into something useful.
            </p>
        </section>
    </div>

    <link rel="stylesheet" href="{{ asset('css/about.css') }}">
</x-app-layout>
