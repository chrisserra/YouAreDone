create table cserraco_youaredone.candidate_flags_backup
(
    candidate_flag_id bigint unsigned default 0                   not null,
    candidate_id      bigint unsigned                             not null,
    election_id       bigint unsigned                             null,
    flag_id           int unsigned                                not null,
    source_id         bigint unsigned                             null,
    weight_override   decimal(8, 2)                               null,
    note              text                                        null,
    is_active         tinyint(1)      default 1                   not null,
    created_at        datetime        default current_timestamp() not null,
    updated_at        datetime        default current_timestamp() not null on update current_timestamp()
);

create table cserraco_youaredone.candidates
(
    candidate_id     bigint unsigned auto_increment
        primary key,
    full_name        varchar(150)                                            not null,
    slug             varchar(255)                                            not null,
    first_name       varchar(100)                                            null,
    middle_name      varchar(100)                                            null,
    last_name        varchar(100)                                            null,
    suffix           varchar(50)                                             null,
    preferred_name   varchar(120)                                            null,
    party_code       varchar(20)                                             null,
    party_name       varchar(100)                                            null,
    website_url      varchar(500)                                            null,
    ballotpedia_url  varchar(500)                                            null,
    wikipedia_url    varchar(500)                                            null,
    x_url            varchar(500)                                            null,
    instagram_url    varchar(500)                                            null,
    facebook_url     varchar(500)                                            null,
    youtube_url      varchar(500)                                            null,
    image_url        varchar(500)                                            null,
    short_bio        text                                                    null,
    summary_public   text                                                    null,
    status           enum ('active', 'archived') default 'active'            not null,
    score_total      decimal(8, 2)               default 0.00                not null,
    green_flag_count int unsigned                default 0                   not null,
    red_flag_count   int unsigned                default 0                   not null,
    created_at       datetime                    default current_timestamp() not null,
    updated_at       datetime                    default current_timestamp() not null on update current_timestamp(),
    constraint uq_candidates_slug
        unique (slug)
);

create index ix_candidates_full_name
    on cserraco_youaredone.candidates (full_name);

create index ix_candidates_name
    on cserraco_youaredone.candidates (last_name, first_name);

create index ix_candidates_party
    on cserraco_youaredone.candidates (party_code);

create index ix_candidates_status_score
    on cserraco_youaredone.candidates (status, score_total);

create table cserraco_youaredone.election_types
(
    election_type_id smallint unsigned auto_increment
        primary key,
    slug             varchar(60)                                   not null,
    name             varchar(100)                                  not null,
    decides_nominee  tinyint(1)        default 0                   not null,
    decides_winner   tinyint(1)        default 0                   not null,
    sort_order       smallint unsigned default 0                   not null,
    is_active        tinyint(1)        default 1                   not null,
    created_at       datetime          default current_timestamp() not null,
    updated_at       datetime          default current_timestamp() not null on update current_timestamp(),
    constraint uq_election_types_name
        unique (name),
    constraint uq_election_types_slug
        unique (slug)
);

create table cserraco_youaredone.flag_categories
(
    category_id int unsigned auto_increment
        primary key,
    slug        varchar(100) not null,
    name        varchar(100) not null,
    sort_order  int          not null,
    constraint slug
        unique (slug)
);

create table cserraco_youaredone.flags
(
    flag_id        int unsigned auto_increment
        primary key,
    slug           varchar(150)                              not null,
    name           varchar(255)                              not null,
    description    text                                      null,
    flag_color     enum ('green', 'red')                     not null,
    default_weight decimal(8, 2) default 1.00                not null,
    is_active      tinyint(1)    default 1                   not null,
    sort_order     int           default 0                   not null,
    created_at     datetime      default current_timestamp() not null,
    updated_at     datetime      default current_timestamp() not null on update current_timestamp(),
    category_id    int unsigned                              null,
    constraint uq_flags_slug
        unique (slug),
    constraint fk_flags_category
        foreign key (category_id) references cserraco_youaredone.flag_categories (category_id)
);

create index ix_flags_active_sort
    on cserraco_youaredone.flags (is_active, sort_order, name);

create table cserraco_youaredone.offices
(
    office_id  smallint unsigned auto_increment
        primary key,
    slug       varchar(50)                                   not null,
    name       varchar(100)                                  not null,
    level      enum ('federal', 'state')                     not null,
    sort_order smallint unsigned default 0                   not null,
    is_active  tinyint(1)        default 1                   not null,
    created_at datetime          default current_timestamp() not null,
    updated_at datetime          default current_timestamp() not null on update current_timestamp(),
    constraint uq_offices_name
        unique (name),
    constraint uq_offices_slug
        unique (slug)
);

create table cserraco_youaredone.races
(
    race_id         bigint unsigned auto_increment
        primary key,
    office_id       smallint unsigned                                                        not null,
    election_year   smallint unsigned                                                        not null,
    state_code      char(2)                                                                  not null,
    state_name      varchar(100)                                                             not null,
    state_slug      varchar(100)                                                             not null,
    district_type   enum ('statewide', 'congressional_district') default 'statewide'         not null,
    district_number smallint unsigned                            default 0                   not null,
    district_label  varchar(100)                                                             null,
    seat_label      varchar(150)                                                             null,
    is_special      tinyint(1)                                   default 0                   not null,
    race_slug       varchar(255)                                                             not null,
    status          enum ('active', 'archived')                  default 'active'            not null,
    notes_public    text                                                                     null,
    created_at      datetime                                     default current_timestamp() not null,
    updated_at      datetime                                     default current_timestamp() not null on update current_timestamp(),
    constraint uq_races_office_year_state_district_special
        unique (office_id, election_year, state_code, district_type, district_number, is_special),
    constraint uq_races_slug
        unique (race_slug),
    constraint fk_races_office
        foreign key (office_id) references cserraco_youaredone.offices (office_id)
            on update cascade
);

create table cserraco_youaredone.elections
(
    election_id        bigint unsigned auto_increment
        primary key,
    race_id            bigint unsigned                                                                    not null,
    election_type_id   smallint unsigned                                                                  not null,
    election_date      date                                                                               not null,
    round_number       tinyint unsigned                                       default 1                   not null,
    title              varchar(255)                                                                       not null,
    slug               varchar(255)                                                                       not null,
    status             enum ('upcoming', 'ongoing', 'completed', 'cancelled') default 'upcoming'          not null,
    filing_deadline    date                                                                               null,
    early_voting_start date                                                                               null,
    early_voting_end   date                                                                               null,
    certification_date date                                                                               null,
    notes_public       text                                                                               null,
    created_at         datetime                                               default current_timestamp() not null,
    updated_at         datetime                                               default current_timestamp() not null on update current_timestamp(),
    constraint uq_elections_race_type_date_round
        unique (race_id, election_type_id, election_date, round_number),
    constraint uq_elections_slug
        unique (slug),
    constraint fk_elections_race
        foreign key (race_id) references cserraco_youaredone.races (race_id)
            on update cascade on delete cascade,
    constraint fk_elections_type
        foreign key (election_type_id) references cserraco_youaredone.election_types (election_type_id)
            on update cascade
);

create table cserraco_youaredone.candidate_sources
(
    source_id    bigint unsigned auto_increment
        primary key,
    candidate_id bigint unsigned                                                                                                            not null,
    election_id  bigint unsigned                                                                                                            null,
    source_type  enum ('official', 'campaign', 'news', 'ballotpedia', 'fec', 'state_filing', 'social', 'other') default 'other'             not null,
    source_name  varchar(255)                                                                                                               not null,
    source_title varchar(500)                                                                                                               null,
    source_url   varchar(1000)                                                                                                              not null,
    published_at datetime                                                                                                                   null,
    retrieved_at datetime                                                                                       default current_timestamp() not null,
    excerpt      text                                                                                                                       null,
    raw_content  mediumtext                                                                                                                 null,
    content_hash char(64)                                                                                                                   null,
    is_active    tinyint(1)                                                                                     default 1                   not null,
    constraint fk_candidate_sources_candidate
        foreign key (candidate_id) references cserraco_youaredone.candidates (candidate_id)
            on update cascade on delete cascade,
    constraint fk_candidate_sources_election
        foreign key (election_id) references cserraco_youaredone.elections (election_id)
            on update cascade on delete set null
);

create table cserraco_youaredone.candidate_flags
(
    candidate_flag_id bigint unsigned auto_increment
        primary key,
    candidate_id      bigint unsigned                        not null,
    flag_id           int unsigned                           not null,
    source_id         bigint unsigned                        null,
    weight_override   decimal(8, 2)                          null,
    note              text                                   null,
    is_active         tinyint(1) default 1                   not null,
    created_at        datetime   default current_timestamp() not null,
    updated_at        datetime   default current_timestamp() not null on update current_timestamp(),
    constraint uq_candidate_flags
        unique (candidate_id, flag_id),
    constraint fk_cf_candidate
        foreign key (candidate_id) references cserraco_youaredone.candidates (candidate_id)
            on update cascade on delete cascade,
    constraint fk_cf_flag
        foreign key (flag_id) references cserraco_youaredone.flags (flag_id)
            on update cascade on delete cascade,
    constraint fk_cf_source
        foreign key (source_id) references cserraco_youaredone.candidate_sources (source_id)
            on update cascade on delete set null
);

create index ix_candidate_flags_candidate
    on cserraco_youaredone.candidate_flags (candidate_id, is_active);

create index ix_candidate_flags_flag
    on cserraco_youaredone.candidate_flags (flag_id, is_active);

create index ix_candidate_flags_source
    on cserraco_youaredone.candidate_flags (source_id);

create table cserraco_youaredone.candidate_flags_old
(
    candidate_flag_id bigint unsigned auto_increment
        primary key,
    candidate_id      bigint unsigned                        not null,
    election_id       bigint unsigned                        null,
    flag_id           int unsigned                           not null,
    source_id         bigint unsigned                        null,
    weight_override   decimal(8, 2)                          null,
    note              text                                   null,
    is_active         tinyint(1) default 1                   not null,
    created_at        datetime   default current_timestamp() not null,
    updated_at        datetime   default current_timestamp() not null on update current_timestamp(),
    constraint uq_candidate_flags
        unique (candidate_id, election_id, flag_id),
    constraint fk_candidate_flags_candidate
        foreign key (candidate_id) references cserraco_youaredone.candidates (candidate_id)
            on update cascade on delete cascade,
    constraint fk_candidate_flags_election
        foreign key (election_id) references cserraco_youaredone.elections (election_id)
            on update cascade on delete set null,
    constraint fk_candidate_flags_flag
        foreign key (flag_id) references cserraco_youaredone.flags (flag_id)
            on update cascade on delete cascade,
    constraint fk_candidate_flags_source
        foreign key (source_id) references cserraco_youaredone.candidate_sources (source_id)
            on update cascade on delete set null
);

create index ix_candidate_flags_candidate
    on cserraco_youaredone.candidate_flags_old (candidate_id, is_active);

create index ix_candidate_flags_election
    on cserraco_youaredone.candidate_flags_old (election_id, is_active);

create index ix_candidate_flags_flag
    on cserraco_youaredone.candidate_flags_old (flag_id, is_active);

create index ix_candidate_sources_candidate
    on cserraco_youaredone.candidate_sources (candidate_id, retrieved_at);

create index ix_candidate_sources_election
    on cserraco_youaredone.candidate_sources (election_id);

create index ix_candidate_sources_type
    on cserraco_youaredone.candidate_sources (source_type);

create table cserraco_youaredone.candidate_updates
(
    update_id    bigint unsigned auto_increment
        primary key,
    candidate_id bigint unsigned                                                                                                                             not null,
    election_id  bigint unsigned                                                                                                                             null,
    source_id    bigint unsigned                                                                                                                             null,
    update_type  enum ('announcement', 'filing', 'endorsement', 'policy', 'controversy', 'campaign_status', 'result', 'general') default 'general'           not null,
    headline     varchar(255)                                                                                                                                not null,
    summary      text                                                                                                                                        not null,
    source_date  date                                                                                                                                        null,
    sort_date    date                                                                                                                                        not null,
    is_public    tinyint(1)                                                                                                      default 1                   not null,
    created_at   datetime                                                                                                        default current_timestamp() not null,
    updated_at   datetime                                                                                                        default current_timestamp() not null on update current_timestamp(),
    constraint fk_candidate_updates_candidate
        foreign key (candidate_id) references cserraco_youaredone.candidates (candidate_id)
            on update cascade on delete cascade,
    constraint fk_candidate_updates_election
        foreign key (election_id) references cserraco_youaredone.elections (election_id)
            on update cascade on delete set null,
    constraint fk_candidate_updates_source
        foreign key (source_id) references cserraco_youaredone.candidate_sources (source_id)
            on update cascade on delete set null
);

create index ix_candidate_updates_candidate_date
    on cserraco_youaredone.candidate_updates (candidate_id, sort_date);

create index ix_candidate_updates_election_date
    on cserraco_youaredone.candidate_updates (election_id, sort_date);

create index ix_candidate_updates_type
    on cserraco_youaredone.candidate_updates (update_type);

create table cserraco_youaredone.election_candidate_flags
(
    election_candidate_flag_id bigint unsigned auto_increment
        primary key,
    election_id                bigint unsigned                        not null,
    candidate_id               bigint unsigned                        not null,
    flag_id                    int unsigned                           not null,
    source_id                  bigint unsigned                        null,
    weight_override            decimal(8, 2)                          null,
    note                       text                                   null,
    is_active                  tinyint(1) default 1                   not null,
    created_at                 datetime   default current_timestamp() not null,
    updated_at                 datetime   default current_timestamp() not null on update current_timestamp(),
    constraint uq_election_candidate_flags
        unique (election_id, candidate_id, flag_id),
    constraint fk_ecf_candidate
        foreign key (candidate_id) references cserraco_youaredone.candidates (candidate_id)
            on update cascade on delete cascade,
    constraint fk_ecf_election
        foreign key (election_id) references cserraco_youaredone.elections (election_id)
            on update cascade on delete cascade,
    constraint fk_ecf_flag
        foreign key (flag_id) references cserraco_youaredone.flags (flag_id)
            on update cascade on delete cascade,
    constraint fk_ecf_source
        foreign key (source_id) references cserraco_youaredone.candidate_sources (source_id)
            on update cascade on delete set null
);

create index ix_ecf_candidate
    on cserraco_youaredone.election_candidate_flags (candidate_id, is_active);

create index ix_ecf_election
    on cserraco_youaredone.election_candidate_flags (election_id, is_active);

create index ix_ecf_flag
    on cserraco_youaredone.election_candidate_flags (flag_id, is_active);

create index ix_ecf_source
    on cserraco_youaredone.election_candidate_flags (source_id);

create table cserraco_youaredone.election_candidates
(
    election_candidate_id bigint unsigned auto_increment
        primary key,
    election_id           bigint unsigned                                                                                               not null,
    candidate_id          bigint unsigned                                                                                               not null,
    ballot_name           varchar(150)                                                                                                  null,
    party_code            varchar(20)                                                                                                   null,
    filing_status         enum ('filed', 'qualified', 'withdrawn', 'removed', 'rumored', 'unknown')         default 'unknown'           not null,
    ballot_status         enum ('on_ballot', 'not_on_ballot', 'pending', 'unknown')                         default 'unknown'           not null,
    result_status         enum ('pending', 'advanced', 'eliminated', 'won', 'lost', 'withdrawn', 'unknown') default 'pending'           not null,
    is_incumbent          tinyint(1)                                                                        default 0                   not null,
    is_major_candidate    tinyint(1)                                                                        default 0                   not null,
    sort_order            int                                                                               default 0                   not null,
    vote_count            bigint unsigned                                                                                               null,
    vote_percent          decimal(7, 3)                                                                                                 null,
    notes_public          text                                                                                                          null,
    created_at            datetime                                                                          default current_timestamp() not null,
    updated_at            datetime                                                                          default current_timestamp() not null on update current_timestamp(),
    constraint uq_election_candidates
        unique (election_id, candidate_id),
    constraint fk_election_candidates_candidate
        foreign key (candidate_id) references cserraco_youaredone.candidates (candidate_id)
            on update cascade on delete cascade,
    constraint fk_election_candidates_election
        foreign key (election_id) references cserraco_youaredone.elections (election_id)
            on update cascade on delete cascade
);

create index ix_election_candidates_candidate
    on cserraco_youaredone.election_candidates (candidate_id);

create index ix_election_candidates_election_sort
    on cserraco_youaredone.election_candidates (election_id, sort_order);

create index ix_election_candidates_result
    on cserraco_youaredone.election_candidates (result_status);

create index ix_elections_date
    on cserraco_youaredone.elections (election_date);

create index ix_elections_race_status
    on cserraco_youaredone.elections (race_id, status);

create index ix_races_office_year
    on cserraco_youaredone.races (office_id, election_year);

create index ix_races_state_slug_year
    on cserraco_youaredone.races (state_slug, election_year);

create index ix_races_state_year
    on cserraco_youaredone.races (state_code, election_year);

create index ix_races_status
    on cserraco_youaredone.races (status);

