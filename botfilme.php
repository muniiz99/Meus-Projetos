<?php
// Chave da API de TMDB
$api_key = "31aa57da784292d1c64c2efb4888da4e";

// Função para pesquisar filmes no TMDB
function pesquisarFilmes($genre, $releaseYear = "")
{
    global $api_key;

    // Montar a URL da API
    $url = "https://api.themoviedb.org/3/discover/movie?api_key=$api_key&language=pt-BR&sort_by=vote_average.desc&with_genres=$genre&include_adult=false&include_video=false&page=1&vote_count.gte=1000";

    // Adicionar o parâmetro do ano de lançamento, se fornecido
    if (!empty($releaseYear)) {
        $url .= "&primary_release_year=$releaseYear";
    }

    // Fazer a requisição à API
    $response = file_get_contents($url);

    // Decodificar a resposta JSON
    $data = json_decode($response, true);

    $total_results = $data["total_results"];
    $results = $data["results"];

    $movie_list = array();
    $counter = 0;

    while ($counter < 50) {
        foreach ($results as $result) {
            if ($counter >= 50) {
                break 2;
            }

            $movie_list[] = array(
                "id" => $result["id"],
                "title" => $result["title"],
                "release_date" => $result["release_date"],
                "poster_path" => $result["poster_path"],
            );

            $counter++;
        }

// Caso a categoria qualquer data não tenha 50 resultados, buscar nas próximas páginas
if ($counter < 50 && $total_results > $counter) {
    $page = ceil($counter / 20) + 1;
    $url = "https://api.themoviedb.org/3/discover/movie?api_key=$api_key&language=pt-BR&sort_by=vote_average.desc&with_genres=$genre&include_adult=false&include_video=false&page=$page&vote_count.gte=1000";

    $response = file_get_contents($url);
    $data = json_decode($response, true);
    $results = $data["results"];

    foreach ($results as $result) {
        if ($counter >= 50) {
            break 2;
        }

        $movie_list[] = array(
            "id" => $result["id"],
            "title" => $result["title"],
            "release_date" => $result["release_date"],
            "poster_path" => $result["poster_path"],
        );

        $counter++;
    }
}

    // Caso a categoria de lançamento não tenha 50 resultados, buscar filmes de anos anteriores
    if ($counter < 50 && !empty($releaseYear)) {
        $year = $releaseYear - 1;

        while ($counter < 50) {
            $url = "https://api.themoviedb.org/3/discover/movie?api_key=$api_key&language=pt-BR&sort_by=vote_average.desc&with_genres=$genre&include_adult=false&include_video=false&page=1&vote_count.gte=1000&primary_release_year=$year";

            $response = file_get_contents($url);
            $data = json_decode($response, true);
            $results = $data["results"];

            foreach ($results as $result) {
                if ($counter >= 50) {
                    break 2;
                }

                $movie_list[] = array(
                    "id" => $result["id"],
                    "title" => $result["title"],
                    "release_date" => $result["release_date"],
                    "poster_path" => $result["poster_path"],
                );

                $counter++;
            }

            $year--;
        }
    }

    return $movie_list;
}
}

// Função para recomendar filmes com base no gênero do usuário
function recomendarFilmes($genre, $releaseYear = "")
{
    $movie_list = pesquisarFilmes($genre, $releaseYear);

    if (empty($movie_list)) {
        return "Desculpe, não encontrei recomendações para esse gênero.";
    }

    $response = "Aqui estão alguns filmes da categoria '$genre'";

    if (!empty($releaseYear)) {
        $response .= " lançados em $releaseYear";
    }

    $formatted_movies = array();
    foreach ($movie_list as $movie) {
        $formatted_movies[] = array(
            "title" => $movie["title"],
            "release_date" => substr($movie["release_date"], 0, 4),
            "poster_path" => $movie["poster_path"],
        );
    }

    return $formatted_movies;
}

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $genre = $_POST["genre"];
    $releaseType = $_POST["release_type"];
    $releaseYear = "";

    if ($releaseType === "same_year") {
        $releaseYear = date("Y");
    }

    $recommendation = recomendarFilmes($genre, $releaseYear);
}

?>
<html>
<head>
    <title>Chatbot de Recomendação de Filmes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: black;
        }
        h1 {
            text-align: center;
            color: white;
        }
        form {
            width: 400px;
            margin: 0 auto;
            text-align: center;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #fff;
        }

        select {
            background-color: #fff;
            width: 100%;
            padding: 5px;
            border: 
            1px solid #ccc;
            border-radius: 4px;
            }

        input[type="submit"] {
            margin-top: 10px;
            padding: 8px 16px;
            background-color: #8b0000;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .recommendation {
            margin-top: 20px;
            text-align: center;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .movie-card {
            background-color: #8b0000;
            padding: 10px;
            border-radius: 10px;
            width: calc(100% / 5 - 40px); /* 5 filmes por linha */
            text-align: center;
        }

        .movie-card img {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
        }

        .movie-info {
            margin-top: 10px;
        }

        .movie-title {
            font-size: 12px;
            text-decoration-color: lightgray;
            font-weight: bold;
            margin-bottom: 5px;
            color: #fff;
        }

        .movie-year {
            font-size: 10px;
            text-decoration-color: lightgray;
            font-weight: bold;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="texto">
        <h1>Chatbot de Recomendação de Filmes</h1>
    <form method="POST">
        <label for="genre">Gênero:</label>
        <select name="genre" id="genre">
            <option value="28">Ação</option>
            <option value="16">Animação</option>
            <option value="12">Aventura</option>
            <option value="35">Comédia</option>
            <option value="80">Crime</option>
            <option value="99">Documentário</option>
            <option value="18">Drama</option>
            <option value="10751">Família</option>
            <option value="14">Fantasia</option>
            <option value="36">História</option>
            <option value="27">Terror</option>
            <option value="10402">Música</option>
            <option value="9648">Mistério</option>
            <option value="10749">Romance</option>
            <option value="878">Ficção Científica</option>
            <option value="10770">TV</option>
            <option value="53">Suspense</option>
            <option value="10752">Guerra</option>
            <option value="37">Faroeste</option>
        </select>
        <label for="release_type">Tipo de Lançamento:</label>
        <select name="release_type" id="release_type">
            <option value="any">Qualquer Data</option>
            <option value="same_year">Lançamento</option>
        </select>
        <input type="submit" value="Pesquisar">
    </form>
    </div>
    <div style="margin-top: 20px;"></div>
    <?php if (isset($recommendation) && is_array($recommendation)): ?>
        <div class="recommendation">
            <?php foreach ($recommendation as $movie): ?>
                <div class="movie-card">
                    <img src="https://image.tmdb.org/t/p/w500<?php echo $movie["poster_path"]; ?>" alt="Poster do filme">
                    <div class="movie-info">
                    <div class="movie-title"><?php echo $movie["title"]; ?></div>
                    <div class="movie-year"><?php echo $movie["release_date"]; ?></div>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

</body>
</html>