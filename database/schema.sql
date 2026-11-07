--
-- PostgreSQL database dump
--

-- Dumped from database version 16.9
-- Dumped by pg_dump version 17.5

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: c99; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.c99 (
    "c99-id" integer NOT NULL,
    "level-1-id" integer,
    ninv text,
    dd text,
    df text,
    dureeintervw numeric(5,0),
    version text,
    enq_reg text,
    enq_nom text,
    statut text
);



--
-- Name: c99_c99-id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public."c99_c99-id_seq"
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



--
-- Name: c99_c99-id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public."c99_c99-id_seq" OWNED BY public.c99."c99-id";


--
-- Name: cgm; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.cgm (
    "level-1-id" integer,
    reg character varying(10),
    pro character varying(100),
    milieu character varying(50)
);



--
-- Name: enqueteurs; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.enqueteurs (
    ninv character varying(50) NOT NULL,
    enq_nom character varying(100),
    statut character varying(50) DEFAULT 'Actif'::character varying,
    commentaire text
);



--
-- Name: level-1; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public."level-1" (
    "level-1-id" integer NOT NULL
);



--
-- Name: level-1_level-1-id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public."level-1_level-1-id_seq"
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



--
-- Name: level-1_level-1-id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public."level-1_level-1-id_seq" OWNED BY public."level-1"."level-1-id";


--
-- Name: users; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.users (
    id integer NOT NULL,
    username character varying(50) NOT NULL,
    password_hash character varying(255) NOT NULL,
    full_name character varying(100),
    email character varying(100),
    role character varying(20) DEFAULT 'utilisateur'::character varying,
    active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT users_role_check CHECK (((role)::text = ANY ((ARRAY['admin'::character varying, 'utilisateur'::character varying])::text[])))
);



--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: vue_anomalies_par_enqueteur; Type: VIEW; Schema: public; Owner: neondb_owner
--

CREATE VIEW public.vue_anomalies_par_enqueteur AS
 SELECT ninv,
    enq_nom,
    count(
        CASE
            WHEN ((df IS NULL) AND (dd IS NOT NULL)) THEN 1
            ELSE NULL::integer
        END) AS anomalies_date_fin_null,
    count(
        CASE
            WHEN (dd IS NULL) THEN 1
            ELSE NULL::integer
        END) AS anomalies_date_debut_null,
    count(
        CASE
            WHEN ((dureeintervw IS NULL) AND (df IS NOT NULL)) THEN 1
            ELSE NULL::integer
        END) AS anomalies_duree_null,
    count(
        CASE
            WHEN (dureeintervw > (300)::numeric) THEN 1
            ELSE NULL::integer
        END) AS anomalies_duree_extreme,
    (((count(
        CASE
            WHEN ((df IS NULL) AND (dd IS NOT NULL)) THEN 1
            ELSE NULL::integer
        END) + count(
        CASE
            WHEN (dd IS NULL) THEN 1
            ELSE NULL::integer
        END)) + count(
        CASE
            WHEN ((dureeintervw IS NULL) AND (df IS NOT NULL)) THEN 1
            ELSE NULL::integer
        END)) + count(
        CASE
            WHEN (dureeintervw > (300)::numeric) THEN 1
            ELSE NULL::integer
        END)) AS anomalies_count
   FROM public.c99 c
  WHERE (ninv IS NOT NULL)
  GROUP BY ninv, enq_nom
 HAVING ((((count(
        CASE
            WHEN ((df IS NULL) AND (dd IS NOT NULL)) THEN 1
            ELSE NULL::integer
        END) + count(
        CASE
            WHEN (dd IS NULL) THEN 1
            ELSE NULL::integer
        END)) + count(
        CASE
            WHEN ((dureeintervw IS NULL) AND (df IS NOT NULL)) THEN 1
            ELSE NULL::integer
        END)) + count(
        CASE
            WHEN (dureeintervw > (300)::numeric) THEN 1
            ELSE NULL::integer
        END)) > 0)
  ORDER BY (((count(
        CASE
            WHEN ((df IS NULL) AND (dd IS NOT NULL)) THEN 1
            ELSE NULL::integer
        END) + count(
        CASE
            WHEN (dd IS NULL) THEN 1
            ELSE NULL::integer
        END)) + count(
        CASE
            WHEN ((dureeintervw IS NULL) AND (df IS NOT NULL)) THEN 1
            ELSE NULL::integer
        END)) + count(
        CASE
            WHEN (dureeintervw > (300)::numeric) THEN 1
            ELSE NULL::integer
        END)) DESC;



--
-- Name: vue_avancement_region; Type: VIEW; Schema: public; Owner: neondb_owner
--

CREATE VIEW public.vue_avancement_region AS
 SELECT enq_reg AS region,
    count(*) AS total_enquetes,
    count(
        CASE
            WHEN (statut = '1'::text) THEN 1
            ELSE NULL::integer
        END) AS enquetes_terminees,
    count(
        CASE
            WHEN (statut = '0'::text) THEN 1
            ELSE NULL::integer
        END) AS enquetes_en_cours,
    round((((count(
        CASE
            WHEN (statut = '1'::text) THEN 1
            ELSE NULL::integer
        END))::numeric * 100.0) / (count(*))::numeric), 2) AS taux_completion,
    count(DISTINCT ninv) AS nombre_enqueteurs,
    avg(
        CASE
            WHEN (dureeintervw IS NOT NULL) THEN dureeintervw
            ELSE NULL::numeric
        END) AS duree_moyenne
   FROM public.c99 c
  WHERE (enq_reg IS NOT NULL)
  GROUP BY enq_reg
  ORDER BY enq_reg;



--
-- Name: vue_c99_anomalie; Type: VIEW; Schema: public; Owner: neondb_owner
--

CREATE VIEW public.vue_c99_anomalie AS
 SELECT ninv,
    enq_nom,
    statut,
    dd,
    df,
    dureeintervw
   FROM public.c99
  WHERE ((statut = 'ANOMALIE'::text) OR (dureeintervw < (5)::numeric) OR (dureeintervw > (180)::numeric));



--
-- Name: vue_collecte_par_jour; Type: VIEW; Schema: public; Owner: neondb_owner
--

CREATE VIEW public.vue_collecte_par_jour AS
 SELECT (dd)::date AS jour,
    count(*) AS enquetes_jour,
    count(
        CASE
            WHEN (statut = 'COMPLETE'::text) THEN 1
            ELSE NULL::integer
        END) AS enquetes_completes,
    round((((count(
        CASE
            WHEN (statut = 'COMPLETE'::text) THEN 1
            ELSE NULL::integer
        END))::numeric * 100.0) / (count(*))::numeric), 2) AS taux_completion_jour
   FROM public.c99
  WHERE (dd IS NOT NULL)
  GROUP BY (dd)::date
  ORDER BY (dd)::date;



--
-- Name: vue_duree_par_enqueteur; Type: VIEW; Schema: public; Owner: neondb_owner
--

CREATE VIEW public.vue_duree_par_enqueteur AS
 SELECT enq_nom,
    count(*) AS total_enquetes,
    round(avg(dureeintervw), 2) AS duree_moyenne,
    min(dureeintervw) AS duree_min,
    max(dureeintervw) AS duree_max
   FROM public.c99
  WHERE ((enq_nom IS NOT NULL) AND (dureeintervw IS NOT NULL))
  GROUP BY enq_nom;



--
-- Name: vue_enquetes_finales; Type: VIEW; Schema: public; Owner: neondb_owner
--

CREATE VIEW public.vue_enquetes_finales AS
 SELECT "c99-id",
    ninv,
    enq_nom,
    enq_reg,
    dd,
    df,
    dureeintervw,
    version,
    statut,
        CASE
            WHEN (statut = '0'::text) THEN 'En cours'::text
            WHEN (statut = '1'::text) THEN 'TerminÃ©'::text
            WHEN (statut = '2'::text) THEN 'Partiel'::text
            WHEN (statut = '3'::text) THEN 'Refus'::text
            ELSE 'Inconnu'::text
        END AS statut_libelle
   FROM public.c99 c
  WHERE (ninv IS NOT NULL);



--
-- Name: vue_enqueteurs_par_region; Type: VIEW; Schema: public; Owner: neondb_owner
--

CREATE VIEW public.vue_enqueteurs_par_region AS
 SELECT enq_reg AS region,
    count(DISTINCT enq_nom) AS nb_enqueteurs,
    count(*) AS total_enquetes,
    round(avg(dureeintervw), 2) AS duree_moyenne
   FROM public.c99
  WHERE ((enq_reg IS NOT NULL) AND (enq_nom IS NOT NULL))
  GROUP BY enq_reg;



--
-- Name: vue_kpi_dashboard; Type: VIEW; Schema: public; Owner: neondb_owner
--

CREATE VIEW public.vue_kpi_dashboard AS
 SELECT count(DISTINCT e.enq_nom) AS total_enqueteurs,
    count(DISTINCT c.ninv) AS total_menages,
    count(DISTINCT
        CASE
            WHEN (c.statut = 'COMPLETE'::text) THEN c.ninv
            ELSE NULL::text
        END) AS menages_complets,
    round((((count(DISTINCT
        CASE
            WHEN (c.statut = 'COMPLETE'::text) THEN c.ninv
            ELSE NULL::text
        END))::numeric * 100.0) / (NULLIF(count(DISTINCT c.ninv), 0))::numeric), 2) AS taux_completion
   FROM (public.enqueteurs e
     CROSS JOIN public.c99 c);



--
-- Name: vue_milieu_par_region; Type: VIEW; Schema: public; Owner: neondb_owner
--

CREATE VIEW public.vue_milieu_par_region AS
 SELECT c.enq_reg AS region,
    g.milieu,
    count(*) AS nombre_enquetes
   FROM (public.c99 c
     LEFT JOIN public.cgm g ON ((c.ninv = (g."level-1-id")::text)))
  WHERE (c.enq_reg IS NOT NULL)
  GROUP BY c.enq_reg, g.milieu;



--
-- Name: vue_rendement_enqueteur; Type: VIEW; Schema: public; Owner: neondb_owner
--

CREATE VIEW public.vue_rendement_enqueteur AS
 SELECT e.ninv,
    e.enq_nom,
    count(c."c99-id") AS total_enquetes,
    count(
        CASE
            WHEN (c.statut = '1'::text) THEN 1
            ELSE NULL::integer
        END) AS enquetes_terminees,
    count(
        CASE
            WHEN (c.statut = '0'::text) THEN 1
            ELSE NULL::integer
        END) AS enquetes_en_cours,
    count(
        CASE
            WHEN (c.statut = '2'::text) THEN 1
            ELSE NULL::integer
        END) AS enquetes_partielles,
    count(
        CASE
            WHEN (c.statut = '3'::text) THEN 1
            ELSE NULL::integer
        END) AS refus,
    round((((count(
        CASE
            WHEN (c.statut = '1'::text) THEN 1
            ELSE NULL::integer
        END))::numeric * 100.0) / (NULLIF(count(c."c99-id"), 0))::numeric), 2) AS taux_completion,
    avg(
        CASE
            WHEN (c.dureeintervw IS NOT NULL) THEN c.dureeintervw
            ELSE NULL::numeric
        END) AS duree_moyenne
   FROM (public.enqueteurs e
     LEFT JOIN public.c99 c ON (((e.ninv)::text = c.ninv)))
  GROUP BY e.ninv, e.enq_nom;



--
-- Name: vue_resume_global; Type: VIEW; Schema: public; Owner: neondb_owner
--

CREATE VIEW public.vue_resume_global AS
 SELECT count(ninv) AS total_enquetes,
    count(
        CASE
            WHEN (statut = 'COMPLETE'::text) THEN 1
            ELSE NULL::integer
        END) AS enquetes_completees,
    count(
        CASE
            WHEN (statut = 'EN_COURS'::text) THEN 1
            ELSE NULL::integer
        END) AS enquetes_en_cours,
    count(
        CASE
            WHEN (statut = 'REFUSE'::text) THEN 1
            ELSE NULL::integer
        END) AS enquetes_refusees,
    round(avg(dureeintervw), 2) AS duree_moyenne_min
   FROM public.c99 c;



--
-- Name: vue_statistiques_globales; Type: VIEW; Schema: public; Owner: neondb_owner
--

CREATE VIEW public.vue_statistiques_globales AS
 SELECT count(*) AS total_enquetes,
    count(
        CASE
            WHEN (statut = '1'::text) THEN 1
            ELSE NULL::integer
        END) AS enquetes_terminees,
    count(
        CASE
            WHEN (statut = '0'::text) THEN 1
            ELSE NULL::integer
        END) AS enquetes_en_cours,
    count(
        CASE
            WHEN (statut = '2'::text) THEN 1
            ELSE NULL::integer
        END) AS enquetes_partielles,
    count(
        CASE
            WHEN (statut = '3'::text) THEN 1
            ELSE NULL::integer
        END) AS refus,
    round((((count(
        CASE
            WHEN (statut = '1'::text) THEN 1
            ELSE NULL::integer
        END))::numeric * 100.0) / (count(*))::numeric), 2) AS taux_completion_global,
    count(DISTINCT ninv) AS nombre_enqueteurs,
    avg(
        CASE
            WHEN (dureeintervw IS NOT NULL) THEN dureeintervw
            ELSE NULL::numeric
        END) AS duree_moyenne_globale
   FROM public.c99;



--
-- Name: vue_statut_detaille; Type: VIEW; Schema: public; Owner: neondb_owner
--

CREATE VIEW public.vue_statut_detaille AS
 SELECT statut,
    count(*) AS nombre,
    round((((count(*))::numeric * 100.0) / sum(count(*)) OVER ()), 2) AS pourcentage
   FROM public.c99
  GROUP BY statut;



--
-- Name: vue_statut_par_enqueteur; Type: VIEW; Schema: public; Owner: neondb_owner
--

CREATE VIEW public.vue_statut_par_enqueteur AS
 SELECT ninv,
    enq_nom,
    total_enquetes,
    enquetes_terminees,
    enquetes_en_cours,
    enquetes_partielles,
    refus,
    taux_completion
   FROM public.vue_rendement_enqueteur
  ORDER BY enq_nom;



--
-- Name: vue_taux_completion_par_enqueteur; Type: VIEW; Schema: public; Owner: neondb_owner
--

CREATE VIEW public.vue_taux_completion_par_enqueteur AS
 SELECT ninv,
    enq_nom,
    total_enquetes,
    enquetes_terminees,
    taux_completion
   FROM public.vue_rendement_enqueteur
  ORDER BY taux_completion DESC;



--
-- Name: vue_taux_suppression; Type: VIEW; Schema: public; Owner: neondb_owner
--

CREATE VIEW public.vue_taux_suppression AS
 SELECT count(
        CASE
            WHEN (statut = 'SUPPRIME'::text) THEN 1
            ELSE NULL::integer
        END) AS total_supprime,
    count(*) AS total_enquetes,
    round((((count(
        CASE
            WHEN (statut = 'SUPPRIME'::text) THEN 1
            ELSE NULL::integer
        END))::numeric * 100.0) / (NULLIF(count(*), 0))::numeric), 2) AS taux_suppression
   FROM public.c99;



--
-- Name: c99 c99-id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.c99 ALTER COLUMN "c99-id" SET DEFAULT nextval('public."c99_c99-id_seq"'::regclass);


--
-- Name: level-1 level-1-id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public."level-1" ALTER COLUMN "level-1-id" SET DEFAULT nextval('public."level-1_level-1-id_seq"'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: c99; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--



--
-- Data for Name: cgm; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--



--
-- Data for Name: enqueteurs; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--



--
-- Data for Name: level-1; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--



--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--



--
-- Name: c99_c99-id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--



--
-- Name: level-1_level-1-id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--



--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--



--
-- Name: c99 c99_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.c99
    ADD CONSTRAINT c99_pkey PRIMARY KEY ("c99-id");


--
-- Name: enqueteurs enqueteurs_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.enqueteurs
    ADD CONSTRAINT enqueteurs_pkey PRIMARY KEY (ninv);


--
-- Name: level-1 level-1_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public."level-1"
    ADD CONSTRAINT "level-1_pkey" PRIMARY KEY ("level-1-id");


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: users users_username_key; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- Name: c99 c99_level-1-id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.c99
    ADD CONSTRAINT "c99_level-1-id_fkey" FOREIGN KEY ("level-1-id") REFERENCES public."level-1"("level-1-id") ON DELETE CASCADE;


--
-- Name: cgm cgm_level-1-id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.cgm
    ADD CONSTRAINT "cgm_level-1-id_fkey" FOREIGN KEY ("level-1-id") REFERENCES public."level-1"("level-1-id");


--
-- Name: DEFAULT PRIVILEGES FOR SEQUENCES; Type: DEFAULT ACL; Schema: public; Owner: cloud_admin
--



--
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: public; Owner: cloud_admin
--



--
-- PostgreSQL database dump complete
--

