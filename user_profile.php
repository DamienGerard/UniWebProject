<?php 
    include 'session_login.php';
    include 'admin/admin_session_login.php';

    $isFollowed = false;
    $isFriend = false;
    $friendButtonText = 'Friend';

    if(isset($_GET['user'])){
        $username = $_GET['user'];
        
        $query = 'SELECT user.user_id FROM webprojectdatabase.user WHERE user.user_name = :username';
            
        $values = array(':username' => $username);
        
        try {
            $res = $pdo->prepare($query);
            $res->execute($values);
        }catch (PDOException $e){
            throw new Exception('Database query error');
        }  

        $thisId = $res->fetch()['user_id'];

        $query = "SELECT follow.date_since FROM webprojectdatabase.follow WHERE follow.followed = :followed AND follow.follower = :follower";
        $values = array(':followed'=>$thisId, ':follower'=>$account->getId()); 

        try {
            $res = $pdo->prepare($query);
            $res->execute($values);
        }catch (PDOException $e){
            throw new Exception('Database query error');
        } 

        if($res->rowCount() > 0) {$isFollowed = true;}

        $query = "SELECT friend.pending FROM webprojectdatabase.friend WHERE (friend.requestee = :requestee AND friend.requestor = :requestor) OR (friend.requestor = :requestee AND friend.requestee = :requestor)";
        $values = array(':requestee'=>$thisId, ':requestor'=>$account->getId()); 

        try {
            $res = $pdo->prepare($query);
            $res->execute($values);
        }catch (PDOException $e){
            throw new Exception('Database query error');
        } 

        if($res->rowCount() > 0) {
            $isFriend = true;
            $row = $res->fetch();
            if($row['pending']==1){
                $friendButtonText = 'pending';
            }else{
                $friendButtonText = 'Friend';
            }
        }else{
            $isFriend = false;
            $friendButtonText = 'Friend';
        }

    }else{
        $thisId = $account->getId();
        
        $query = 'SELECT user.user_name FROM webprojectdatabase.user WHERE user.user_id = :user_id';
            
        $values = array(':user_id' => $thisId);
        
        try {
            $res = $pdo->prepare($query);
            $res->execute($values);
        }catch (PDOException $e){
            throw new Exception('Database query error');
        }  

        $username = $res->fetch()['user_name'];
    }

    $query = 'SELECT * FROM webprojectdatabase.user WHERE (user_id=:id)';
    $values = array(':id'=>$thisId);
    try
    {
        $res = $pdo->prepare($query);
        $res->execute($values);
    }
    catch (PDOException $e)
    {
        throw new Exception('Database query error');
    }

    $row = $res->fetch(PDO::FETCH_ASSOC);
    
    $fname = $row['f_name'];
    $lname = $row['l_name'];
    $self_description = $row['self_description'];
    $dob = $row['DOB'];
    $education = $row['education'];
    $profile_pic = $row['profile_pic'];
    $cover_pic = $row['cover_pic']
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $fname.' '.$lname; ?></title>
    <link rel="stylesheet" href="userProfile.css">
    <link rel="stylesheet" href="tab.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://kit.fontawesome.com/e279e577b6.js"></script>

    
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

</head>
<body>
     
    <?php include 'navbar.php';?>

    <div class="super-main">

        <?php include 'side_column.php';?>

        <div class="main">
            <div id="reload-twPc-div"><div id="twPc-div" class="twPc-div" style="border: 1px solid black; width:95%;">
                <div id="cover" class="twPc-bg twPc-block"><img id="coverPic" src='<?php echo "$cover_pic";?>' style="width:100%"></div>

                <div id="profile"><img id="profilePic" src='<?php echo "$profile_pic";?>' alt="" class="twPc-avatarImg" style="border: 3px solid black;"></div>

                <div id="twPc-divUser" class="twPc-divUser" >
                    <div class="twPc-divName">
                        <a href="user_profile.php?user=<?php echo $username;?>"><?php echo $fname;?> <?php echo $lname;?></a>
                    </div>
                    <span style="margin-left:30%;">
                    @<?php echo "$username";?>
                    </span>

                    <?php if($thisId==$account->getId()){ ?>
                    <button id="friendMain" class="<?php if($isFriend){echo 'in-relation-button';}else{echo 'relation-button';} ?>" <?php if($login){?> onclick="location.href='friend_main.php';" <?php } ?> ><?php echo "Friends" ?></button>
                    <?php } ?>

                    <?php if($thisId!=$account->getId()){ ?>
                    <button id="friendButton" class="<?php if($isFriend){echo 'in-relation-button';}else{echo 'relation-button';} ?>" <?php if($login){?> onclick="processFriend()" <?php } ?> ><?php echo $friendButtonText ?></button>
                    <?php }else{ ?>
                    <button id="followerMain" class="relation-button" onclick="location.href='follower_main.php';">Followers</button>
                    <?php } ?>
                        
                    <?php if($thisId!=$account->getId()){ ?>
                    <button id="followButton" class="<?php if($isFollowed){echo 'in-relation-button';}else{echo 'relation-button';} ?>" <?php if($login){?> onclick="processFollow()" <?php } ?>><?php if($isFollowed){echo 'Following';}else{echo 'Follow';} ?></button>
                    <?php }else{ ?>
                    <button id="followingMain" class="relation-button" onclick="location.href='following_main.php';" >Followings</button>
                    <?php } ?>
                    
                    <script>
                        function processFollow(){
                            $.post( "processFollow.php", { follower: "<?php echo $account->getId(); ?>", followed: "<?php echo $thisId; ?>" })
                            .done(function( data ) {
                                document.getElementById('followButton').outerHTML=data;
                            });
                        }

                        function processFriend(){
                            $.post( "processFriend.php", { requestor: "<?php echo $account->getId(); ?>", requestee: "<?php echo $thisId; ?>" })
                            .done(function( data ) {
                                document.getElementById('friendButton').outerHTML=data;
                            });
                        }
                    </script>

                </div>

                <div class="twPc-divStats">
                    <br>
                    <ul class="twPc-Arrange">
                        <li class="twPc-ArrangeSizeFit">
                            
                                <span class="twPc-StatLabel twPc-block">Date of Birth</span>
                                <span id="dateOfBirth" class="twPc-StatValue"><?php echo "$dob";?></span>
                        
                        </li>
                        <li class="twPc-ArrangeSizeFit">
                            
                                <span class="twPc-StatLabel twPc-block">Education</span>
                                <span id="educ" class="twPc-StatValue"><?php echo "$education";?></span>
                            
                        </li>
                        <li class="twPc-ArrangeSizeFit">
                            
                                <span class="twPc-StatLabel twPc-block">About myself</span>
                                <span id="self" class="twPc-StatValue"><?php echo "$self_description";?></span>
                            
                        </li>
                        <!-- Trigger the modal with a button -->
                    <?php if($login && $thisId==$id){ ?><button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal1">Edit Profile</button><?php } ?>
                    </ul>
                </div>
            </div>
        </div>   

        <div id="myModal1" class="modal fade" role="dialog">
            <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Profile</h4>
            </div>
            <div class="modal-body">
            <form id="updateProfile">
                <div class="form-group">
                        <label>Cover Picture:</label>
                        <input id="cover-image-upload" value = <?php echo $cover_pic;?> type="file" name="coverImage"/>
                        <img src='<?php echo "$cover_pic";?>' id="cover-image" alt="" class="twPc-avatarImg" style="border-radius: 20%;height:70px;width:70px;margin-top:4%;">
                    </div>
                <div class="form-group">
                        <label>Profile Picture:</label>
                        <input id="profile-image-upload" value = <?php echo $profile_pic;?> type="file"  name="profileImage"/>
                        <img src='<?php echo "$profile_pic";?>' id="profile-image" alt="" class="twPc-avatarImg" style="border-radius: 20%;height:70px;width:70px;margin-top:4%;">
                    </div>
                    <div class="form-group">
                        <label>First Name:</label>
                        <input type="text" id="firstName" name="firstName" class="form-control" value=<?php echo $fname;?>>
                    </div>
                    <div class="form-group">
                        <label>Last Name:</label>
                        <input type="text" id="lastName" name="lastName" class="form-control" value=<?php echo $lname;?>>
                    </div>

                    <div class="form-group">
                        <label>Date of Birth:</label>
                        <input type="date" id="dob" name="dob" class="form-control" value=<?php echo "$dob";?>>
                    </div>
                    <div class="form-group">
                        <label>Education:</label>
                        <textarea id="education" name="education" class="md-textarea form-control"  rows="4" cols="20"><?php echo "$education";?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Self-Description:</label>
                        <textarea id="description" name="selfDescription" class="md-textarea form-control" placeholder="About You"  rows="4" cols="20" ><?php echo "$self_description";?></textarea>
                    </div>
            </form>
            </div>
            <div class="modal-footer">
            <button type="button" id="update" class="btn btn-primary pull-right" >Update</button>
            
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            
            </div>
        </div>

        <script>
            $(document).ready(function(){
                $("button#update").on('click', function(){
                    let formData = new FormData();   
                    formData.append('firstName', $("#firstName").val()); 
                    formData.append('lastName', $("#lastName").val());
                    formData.append('dob', $("#dob").val());
                    formData.append('education', $("#education").val());
                    formData.append('selfDescription', $("#description").val());
                    if($('#cover-image-upload').get(0).files.length !== 0){
                        console.log("Cover picture exists!");
                        formData.append('coverImage', $("#cover-image-upload")[0].files[0], $("#cover-image-upload").val().split('\\').pop());
                    }

                    if($('#profile-image-upload').get(0).files.length !== 0){
                        console.log("Profile picture exists!");
                        formData.append('profileImage', $("#profile-image-upload")[0].files[0], $("#profile-image-upload").val().split('\\').pop());
                    }
                    
                    $.ajax({
                        url:"edit_user_profile.php",
                        data: formData,
                        cache: false,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        success: function(response){
                            alert("Olryte");
                            console.log(response);
                            $("#reload-twPc-div").load(location.href + " #twPc-div");
                        }
                    })
                });

                $("#profile-image").click(function(){
                    $("#profile-image-upload").click();
                });
                $("#cover-image").click(function(){
                    $("#cover-image-upload").click();
                });
            });
        </script>
  </div>
</div>


            <div style="width:100%; dsplay:flex;">
                <div class="user-tabinator-container" style="float:left; width:75%;">
                    <?php
                        $queryArticle = 'SELECT content.content_id, content.title, content.date, user.f_name, user.l_name, user.profile_pic, user.user_name, article.text FROM webprojectdatabase.content INNER JOIN webprojectdatabase.user ON content.user_id = user.user_id INNER JOIN webprojectdatabase.article ON article.article_id = content.content_id WHERE content.user_id='.$thisId.' AND content.marked=0 ORDER BY content.date DESC';

                        $queryImage = 'SELECT content.content_id, content.title, content.date, user.f_name, user.l_name, user.profile_pic, user.user_name, picture.picture_path FROM webprojectdatabase.content INNER JOIN webprojectdatabase.user ON content.user_id = user.user_id INNER JOIN webprojectdatabase.picture ON picture.picture_id = content.content_id WHERE content.user_id='.$thisId.' AND content.marked=0 ORDER BY content.date DESC';

                        $queryVideo = 'SELECT content.content_id, content.title, content.date, user.f_name, user.l_name, user.profile_pic, user.user_name, video.thumbnail_path FROM webprojectdatabase.content INNER JOIN webprojectdatabase.user ON content.user_id = user.user_id INNER JOIN webprojectdatabase.video ON video.video_id = content.content_id WHERE content.user_id='.$thisId.' AND content.marked=0 ORDER BY content.date DESC';

                        $queryQuestion = 'SELECT * FROM webprojectdatabase.content INNER JOIN webprojectdatabase.user ON content.user_id = user.user_id INNER JOIN webprojectdatabase.question ON question.question_id = content.content_id WHERE content.user_id='.$thisId.' AND content.marked=0 ORDER BY content.date DESC';
                        
                        include 'content_tabinator.php';
                    ?>
                </div>


                <?php 
                    $subject_type = 'user';
                    include 'interest_tab.php';
                ?>                
            </div>

        </div>    
    </div>


    <?php include 'footer.php';?>
</body>
</html>