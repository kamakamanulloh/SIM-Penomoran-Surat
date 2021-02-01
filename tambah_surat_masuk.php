<?php
    //cek session
    if(empty($_SESSION['admin'])){
        $_SESSION['err'] = '<center>Anda harus login terlebih dahulu!</center>';
        header("Location: ./");
        die();
    } else {
      
        $bulan = date('n');
        $romawi = getRomawi($bulan);
        $tahun = date ('Y');
        $nomor = "/PKP/".$romawi."/".$tahun;
        
        // membaca kode / nilai tertinggi dari penomoran yang ada didatabase berdasarkan tanggal
         $sql = mysqli_query($config, "SELECT no_agenda FROM tbl_surat_masuk");
         $no_agenda = "1";
         if (mysqli_num_rows($sql) == 0){
                $noUrut= $no_agenda;
             }

         $result = mysqli_num_rows($sql);
         $counter = 0;
         while(list($no_agenda) = mysqli_fetch_array($sql)){
         if (++$counter == $result) {
             $no_agenda++;
             $noUrut= $no_agenda++;
            }
         }
        
        //membuat Nomor Surat Otomatis dengan awalan depan 0 misalnya , 01,02 
        //jika ingin 003 ,tinggal ganti %03
        $kode =  sprintf("%02s", $noUrut);
        $nomorbaru = $kode.$nomor;


        if(isset($_REQUEST['submit'])){

            //validasi form kosong
            if( $_REQUEST['kode_surat'] == "" || $_REQUEST['asal_surat'] == "" || $_REQUEST['isi'] == ""
                || $_REQUEST['kegiatan'] == "" || $_REQUEST['penanggung_jawab'] == "" || $_REQUEST['tgl_surat'] == ""  || $_REQUEST['perihal'] == ""){
                $_SESSION['errEmpty'] = 'ERROR! Semua form wajib diisi';
            echo $_REQUEST['kode_surat'].", ".$_REQUEST['asal_surat'].", ".$_REQUEST['isi'].", ".$_REQUEST['kegiatan'].", ".$_REQUEST['penanggung_jawab'];
                // echo '<script language="javascript">window.history.back();</script>';
            } else {

                $no_agenda = $_REQUEST['no_agenda'];
                $no_surat = $_REQUEST['kode_surat'];
                $asal_surat = $_REQUEST['asal_surat'];
                $isi = $_REQUEST['isi'];
               
                $penanggung_jawab = $_REQUEST['penanggung_jawab'];
                $tgl_surat = $_REQUEST['tgl_surat'];
                $kegiatan = $_REQUEST['kegiatan'];
                $perihal = $_REQUEST['perihal'];
                $tujuan_surat = $_REQUEST['tujuan_surat'];
                $id_user = $_SESSION['id_user'];

                //validasi input data
                if(!preg_match("/^[0-9]*$/", $no_agenda)){
                    $_SESSION['no_agenda'] = 'Form Nomor Agenda harus diisi angka!';
                    echo '<script language="javascript">window.history.back();</script>';
                } else {

                    if(!preg_match("/^[a-zA-Z0-9.\/ -]*$/", $no_surat)){
                        $_SESSION['no_surat'] = 'Form No Surat hanya boleh mengandung karakter huruf, angka, spasi, titik(.), minus(-) dan garis miring(/)';
                        echo '<script language="javascript">window.history.back();</script>';
                    } else {

                        if(!preg_match("/^[a-zA-Z0-9.,() \/ -]*$/", $asal_surat) || !preg_match("/^[a-zA-Z0-9.,() \/ -]*$/", $tujuan_surat)){
                            $_SESSION['asal_surat'] = 'Form Asal Surat hanya boleh mengandung karakter huruf, angka, spasi, titik(.), koma(,), minus(-),kurung() dan garis miring(/)';
                              $_SESSION['tujuan_surat'] = 'Form Asal Surat hanya boleh mengandung karakter huruf, angka, spasi, titik(.), koma(,), minus(-),kurung() dan garis miring(/)';
                            echo '<script language="javascript">window.history.back();</script>';
                        } else {

                            if(!preg_match("/^[a-zA-Z0-9.,_()%&@\/\r\n -]*$/", $isi)){
                                $_SESSION['isi'] = 'Form Isi Ringkas hanya boleh mengandung karakter huruf, angka, spasi, titik(.), koma(,), minus(-), garis miring(/), kurung(), underscore(_), dan(&) persen(%) dan at(@)';
                                echo '<script language="javascript">window.history.back();</script>';
                            } else {

                                    if(!preg_match("/^[a-zA-Z0-9.,() \/ -]*$/", $perihal)){
                                        $_SESSION['indeks'] = 'Form Indeks hanya boleh mengandung karakter huruf, angka, spasi, titik(.) dan koma(,) dan minus (-)';
                                        echo '<script language="javascript">window.history.back();</script>';
                                    } else {

                                        if(!preg_match("/^[0-9.-]*$/", $tgl_surat)){
                                            $_SESSION['tgl_surat'] = 'Form Tanggal Surat hanya boleh mengandung angka dan minus(-)';
                                            echo '<script language="javascript">window.history.back();</script>';
                                        } else {

                                            if(!preg_match("/^[a-zA-Z0-9.,()\/ -]*$/", $isi)){
                                                $_SESSION['isi'] = 'Form Keterangan hanya boleh mengandung karakter huruf, angka, spasi, titik(.), koma(,), minus(-), garis miring(/), dan kurung()';
                                                echo '<script language="javascript">window.history.back();</script>';
                                            } else {

                                                $cek = mysqli_query($config, "SELECT * FROM tbl_surat_masuk WHERE no_surat='$no_surat'");
                                                $result = mysqli_num_rows($cek);

                                                if($result > 0){
                                                    $_SESSION['errDup'] = 'Nomor Surat sudah terpakai, gunakan yang lain!';
                                                    echo '<script language="javascript">window.history.back();</script>';
                                                } else {

                                                    $ekstensi = array('jpg','png','jpeg','doc','docx','pdf');
                                                    $file = $_FILES['file']['name'];
                                                    $x = explode('.', $file);
                                                    $eks = strtolower(end($x));
                                                    $ukuran = $_FILES['file']['size'];
                                                    $target_dir = "upload/surat_masuk/";

                                                    if (! is_dir($target_dir)) {
                                                        mkdir($target_dir, 0755, true);
                                                    }

                                                    //jika form file tidak kosong akan mengeksekusi script dibawah ini
                                                    if($file != ""){

                                                        $rand = rand(1,10000);
                                                        $nfile = $rand."-".$file;

                                                        //validasi file
                                                        if(in_array($eks, $ekstensi) == true){
                                                            if($ukuran < 2500000){

                                                                move_uploaded_file($_FILES['file']['tmp_name'], $target_dir.$nfile);

                                                                $sql =  "INSERT INTO tbl_surat_masuk(no_agenda,no_surat,asal_surat,tujuan_surat,isi,perihal,tgl_surat,
                                                                    tgl_diterima,file,penanggung_jawab,kegiatan,id_user)
                                                                        VALUES('$no_agenda','$no_surat','$asal_surat','$tujuan_surat','$isi','$perihal','$tgl_surat',NOW(),'$nfile','$penanggung_jawab','$kegiatan','$id_user')";

                                                                $query=mysqli_query($config,$sql);


                                                                if($query == true){
                                                                    $_SESSION['succAdd'] = 'SUKSES! Data berhasil ditambahkan';
                                                                    header("Location: ./admin.php?page=tsm");
                                                                    die();
                                                                } else {
                                                                    echo $sql;
                                                                    $_SESSION['errQ'] = 'ERROR! Ada masalah dengan query';
                                                                    // echo '<script language="javascript">window.history.back();</script>';
                                                                    echo $sql;
                                                                }
                                                            } else {
                                                                $_SESSION['errSize'] = 'Ukuran file yang diupload terlalu besar!';
                                                                echo '<script language="javascript">window.history.back();</script>';
                                                            }
                                                        } else {
                                                            $_SESSION['errFormat'] = 'Format file yang diperbolehkan hanya *.JPG, *.PNG, *.DOC, *.DOCX atau *.PDF!';
                                                            echo '<script language="javascript">window.history.back();</script>';
                                                        }
                                                    } else {

                                                        //jika form file kosong akan mengeksekusi script dibawah ini
                                                      $sql =  "INSERT INTO tbl_surat_masuk(no_agenda,no_surat,asal_surat,tujuan_surat,isi,perihal,tgl_surat,
                                                                    tgl_diterima,penanggung_jawab,kegiatan,id_user)
                                                                        VALUES('$no_agenda','$no_surat','$asal_surat','$tujuan_surat','$isi','$perihal','$tgl_surat',NOW(),'$penanggung_jawab','$kegiatan','$id_user')";

                                                       $query=mysqli_query($config,$sql);

                                                        if($query == true){
                                                            $_SESSION['succAdd'] = 'SUKSES! Data berhasil ditambahkan';
                                                            header("Location: ./admin.php?page=tsm");
                                                            die();
                                                        } else {
                                                            $_SESSION['errQ'] = 'ERROR! Ada masalah dengan query';
                                                             echo '<script language="javascript">window.history.back();</script>';
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        else {?>

            <!-- Row Start -->
            <div class="row">
                <!-- Secondary Nav START -->
                <div class="col s12">
                    <nav class="secondary-nav">
                        <div class="nav-wrapper blue-grey darken-1">
                            <ul class="left">
                                <li class="waves-effect waves-light"><a href="?page=tsm&act=add" class="judul"><i class="material-icons">mail</i> Tambah Data Surat Masuk</a></li>
                            </ul>
                        </div>
                    </nav>
                </div>
                <!-- Secondary Nav END -->
            </div>
            <!-- Row END -->

            <?php
                if(isset($_SESSION['errQ'])){
                    $errQ = $_SESSION['errQ'];
                    echo '<div id="alert-message" class="row">
                            <div class="col m12">
                                <div class="card red lighten-5">
                                    <div class="card-content notif">
                                        <span class="card-title red-text"><i class="material-icons md-36">clear</i> '.$errQ.'</span>
                                    </div>
                                </div>
                            </div>
                        </div>';
                    unset($_SESSION['errQ']);
                }
                if(isset($_SESSION['errEmpty'])){
                    $errEmpty = $_SESSION['errEmpty'];
                    echo '<div id="alert-message" class="row">
                            <div class="col m12">
                                <div class="card red lighten-5">
                                    <div class="card-content notif">
                                        <span class="card-title red-text"><i class="material-icons md-36">clear</i> '.$errEmpty.'</span>
                                    </div>
                                </div>
                            </div>
                        </div>';
                    unset($_SESSION['errEmpty']);
                }
            ?>

            <!-- Row form Start -->
            <div class="row jarak-form">

                <!-- Form START -->
                <form class="col s12" method="POST" action="?page=tsm&act=add" enctype="multipart/form-data">

                     <?php
                            echo '<input id="no_agenda" type="hidden" class="validate" name="no_agenda" value="';
                                $sql = mysqli_query($config, "SELECT no_agenda FROM tbl_surat_masuk");
                                $no_agenda = "1";
                                if (mysqli_num_rows($sql) == 0){
                                    echo $no_agenda;
                                }

                                $result = mysqli_num_rows($sql);
                                $counter = 0;
                                while(list($no_agenda) = mysqli_fetch_array($sql)){
                                    if (++$counter == $result) {
                                        $no_agenda++;
                                        echo $no_agenda;
                                    }
                                }
                                echo '" required>';?>

                    <!-- Row in form START -->
                    <div class="row">
                      <!--  -->
                   <div class="input-field col s6">
                            <i class="material-icons prefix md-prefix">looks_two</i>
                            <input id="kode_surat" type="text" class="validate"  name="kode_surat" value="<?php echo $nomorbaru ?>" required>
                                <?php
                                    if(isset($_SESSION['kode_surat'])){
                                        $no_surat = $_SESSION['kode_surat'];
                                        echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">'.$no_surat.'</div>';
                                        unset($_SESSION['kode_surat']);
                                    }
                                    if(isset($_SESSION['errDup'])){
                                        $errDup = $_SESSION['errDup'];
                                        echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">'.$errDup.'</div>';
                                        unset($_SESSION['errDup']);
                                    }
                                ?>
                            <label for="kode_surat">Nomor Surat</label>
                        </div>
                         <div class="input-field col s6">
                            <i class="material-icons prefix md-prefix">bookmark</i>
                            <input id="kegiatan" type="text" class="validate" name="kegiatan" required>
                                <?php
                                    if(isset($_SESSION['kegiatan'])){
                                        $kegiatan = $_SESSION['kegiatan'];
                                        echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">'.$kegiatan.'</div>';
                                        unset($_SESSION['kegiatan']);
                                    }
                                ?>
                            <label for="kegiatan">Kegiatan</label>
                        </div>
                        <div class="input-field col s6">
                            <i class="material-icons prefix md-prefix">place</i>
                            <input id="asal_surat" type="text" class="validate" name="asal_surat" required>

                                <?php
                                    if(isset($_SESSION['asal_surat'])){
                                        $asal_surat = $_SESSION['asal_surat'];
                                        echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">'.$asal_surat.'</div>';
                                        unset($_SESSION['asal_surat']);
                                    }
                                ?>
                            <label for="asal_surat">Asal Surat</label>
                        </div>
                         <div class="input-field col s6">
                            <i class="material-icons prefix md-prefix">place</i>
                            <input id="tujuan_surat" type="text" class="validate" name="tujuan_surat" required>

                                <?php
                                    if(isset($_SESSION['tujuan_surat'])){
                                        $asal_surat = $_SESSION['tujuan_surat'];
                                        echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">'.$asal_surat.'</div>';
                                        unset($_SESSION['asal_surat']);
                                    }
                                ?>
                            <label for="asal_surat">Tujuan Surat</label>
                        </div>
                        <div class="input-field col s6">
                            <i class="material-icons prefix md-prefix">storage</i>
                            <input id="perihal" type="text" class="validate" name="perihal" required>
                            <!-- <div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text"></div> -->
                                <?php
                                    if(isset($_SESSION['perihal'])){
                                        $perihal = $_SESSION['perihal'];
                                        echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">'.$perihal.'</div>';
                                        unset($_SESSION['perihal']);
                                    }
                                ?>
                            <label for="perihal">Perihal</label>
                        </div>
                      
                        <div class="input-field col s6">
                            <i class="material-icons prefix md-prefix">date_range</i>
                            <input id="tgl_surat" type="text" name="tgl_surat" class="datepicker" required>
                                <?php
                                    if(isset($_SESSION['tgl_surat'])){
                                        $tgl_surat = $_SESSION['tgl_surat'];
                                        echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">'.$tgl_surat.'</div>';
                                        unset($_SESSION['tgl_surat']);
                                    }
                                ?>
                            <label for="tgl_surat">Tanggal Surat</label>
                        </div>
                        <div class="input-field col s6">
                            <i class="material-icons prefix md-prefix">description</i>
                            <textarea id="isi" class="materialize-textarea validate" name="isi" required></textarea>
                                <?php
                                    if(isset($_SESSION['isi'])){
                                        $isi = $_SESSION['isi'];
                                        echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">'.$isi.'</div>';
                                        unset($_SESSION['isi']);
                                    }
                                ?>
                            <label for="isi">Keterangan</label>
                        </div>
                        <div class="input-field col s6">
                            <i class="material-icons prefix md-prefix">person</i>
                            <input id="penanggung_jawab" type="text" class="validate" name="penanggung_jawab" required>
                                <?php
                                    if(isset($_SESSION['penanggung_jawab'])){
                                        $penanggung_jawab = $_SESSION['penanggung_jawab'];
                                        echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">'.$penanggung_jawab.'</div>';
                                        unset($_SESSION['penanggung_jawab']);
                                    }
                                ?>
                            <label for="penanggung_jawab">Penanggung jawab</label>
                        </div>
                        <div class="input-field col s6">
                            <div class="file-field input-field">
                                <div class="btn light-green darken-1">
                                    <span>File</span>
                                    <input type="file" id="file" name="file">
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text" placeholder="Upload file/scan gambar surat masuk">
                                        <?php
                                            if(isset($_SESSION['errSize'])){
                                                $errSize = $_SESSION['errSize'];
                                                echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">'.$errSize.'</div>';
                                                unset($_SESSION['errSize']);
                                            }
                                            if(isset($_SESSION['errFormat'])){
                                                $errFormat = $_SESSION['errFormat'];
                                                echo '<div id="alert-message" class="callout bottom z-depth-1 red lighten-4 red-text">'.$errFormat.'</div>';
                                                unset($_SESSION['errFormat']);
                                            }
                                        ?>
                                    <small class="red-text">*Format file yang diperbolehkan *.JPG, *.PNG, *.DOC, *.DOCX, *.PDF dan ukuran maksimal file 2 MB!</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Row in form END -->

                    <div class="row">
                        <div class="col 6">
                            <button type="submit" name="submit" class="btn-large blue waves-effect waves-light">SIMPAN <i class="material-icons">done</i></button>
                        </div>
                        <div class="col 6">
                            <a href="?page=tsm" class="btn-large deep-orange waves-effect waves-light">BATAL <i class="material-icons">clear</i></a>
                        </div>
                    </div>

                </form>
                <!-- Form END -->

            </div>
            <!-- Row form END -->

<?php
        }
    }
?>
