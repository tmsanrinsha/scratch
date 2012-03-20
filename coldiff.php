<?php 
 
// RGB values
$red = 39;
$green = 176;
$blue = 165;
function rgb_to_xyz($rgb){
    $red = $rgb[0];
    $green = $rgb[1];
    $blue = $rgb[2];

    // same values, from 0 to 1
    $_red = $red/255;
    $_green = $green/255;
    $_blue = $blue/255;

    // adjusting values
    if($_red>0.04045){
        $_red = ($_red+0.055)/1.055;
        $_red = pow($_red,2.4);
    }
    else{
        $_red = $_red/12.92;
    }
    if($_green>0.04045){
        $_green = ($_green+0.055)/1.055;
        $_green = pow($_green,2.4);     
    }
    else{
        $_green = $_green/12.92;
    }
    if($_blue>0.04045){
        $_blue = ($_blue+0.055)/1.055;
        $_blue = pow($_blue,2.4);     
    }
    else{
        $_blue = $_blue/12.92;
    }

    $_red *= 100;
    $_green *= 100;
    $_blue *= 100;

    // applying the matrix
    $x = $_red * 0.4124 + $_green * 0.3576 + $_blue * 0.1805;
    $y = $_red * 0.2126 + $_green * 0.7152 + $_blue * 0.0722;
    $z = $_red * 0.0193 + $_green * 0.1192 + $_blue * 0.9505;

    // displaying the values
    //echo "$x $y $z";
    return array($x, $y, $z);
}
 
function xyz_to_Lab($xyz) {
    $x = $xyz[0];
    $y = $xyz[1];
    $z = $xyz[2];

    $_x = $x/95.047;
    $_y = $y/100;
    $_z = $z/108.883;

    // adjusting the values
    if($_x>0.008856){
        $_x = pow($_x,1/3);
    }
    else{
        $_x = 7.787*$_x + 16/116;
    }
    if($_y>0.008856){
        $_y = pow($_y,1/3);
    }
    else{
        $_y = (7.787*$_y) + (16/116);
        //echo $_y."<br>";
    }
    if($_z>0.008856){
        $_z = pow($_z,1/3);
    }
    else{
        $_z = 7.787*$_z + 16/116;
    }

    $l= 116*$_y -16;
    $a= 500*($_x-$_y);
    $b= 200*($_y-$_z);

    // displaying the values
    //echo "$l $a $b";
    return array($l, $a, $b);
}

function CIE76($lab1, $lab2) {
    return sqrt(pow($lab1[0] - $lab2[0],2) + pow($lab1[1] - $lab2[1],2) + pow($lab1[2] - $lab2[2],2));
}

function rgb_CIE76($rgb1, $rgb2) {
    return CIE76(xyz_to_Lab(rgb_to_xyz($rgb1)), xyz_to_Lab(rgb_to_xyz($rgb2)));
}

function CIE94($lab1, $lab2) {
    $deltaL = $lab1[0] - $lab2[0];
    $c1 = sqrt(pow($lab1[1],2) + pow($lab1[2],2));
    $c2 = sqrt(pow($lab2[1],2) + pow($lab2[2],2));
    $deltaCab = $c1 - $c2;
    $deltaHab = sqrt(pow(CIE76($lab1, $lab2),2) - pow($deltaL, 2) - pow($deltaCab,2));
    //echo $deltaHab . "\n";
    $deltaHab2 = sqrt(pow($lab1[1] - $lab2[1],2) + pow($lab1[2] - $lab2[2], 2) - pow($deltaCab,2));
    //echo $deltaHab2 . "\n";
    $deltaHab = 0;

    $KL = 1;
    $K1 = 0.045;
    $K2 = 0.015;

    return sqrt(pow($deltaL/$KL,2) + pow($deltaCab / (1 + $K1*$c1),2) + pow($deltaHab/(1 + $K2*$c1),2));
}

function rgb_CIE94($rgb1, $rgb2) {
    return CIE94(xyz_to_Lab(rgb_to_xyz($rgb1)), xyz_to_Lab(rgb_to_xyz($rgb2)));
}
    
//print_r(xyz_to_Lab(rgb_to_xyz(array(39, 176, 165))));
$lab = array(4.03, -3.05, +1.04);
//print CIE76($lab, array(0,0,0));

# now the color cube
//print "Color cube, 6x6x6:\n";
for ($green = 0; $green < 6; $green++) {
    for ($red = 0; $red < 6; $red++) {
    for ($blue = 0; $blue < 6; $blue++) {
        $color = 16 + ($red * 36) + ($green * 6) + $blue;
        $rgb = array(
            ($red ? ($red * 40 + 55) : 0),
            ($green ? ($green * 40 + 55) : 0),
            ($blue ? ($blue * 40 + 55) : 0)
        );
        $black = array(0, 0, 0);
       #print "\x1b[48;5;${color}m  ";
        //print "\x1b[38;5;${color}m$color\x1b[0m \n$color,diff:" . rgb_CIE76($rgb, $black). "\n";
        //print "\x1b[38;5;${color}m$color\x1b[0m ,rgb($rgb[0], $rgb[1], $rgb[2])\n";
        //print round(rgb_CIE94($rgb, $black)) . " \x1b[38;5;${color}m$color\x1b[0m\n";
        print round(rgb_CIE76($rgb, $black)) . " \x1b[38;5;${color}m$color\x1b[0m\n";
        //print 'CIE76: ' . rgb_CIE76($rgb, $black) . "\n";
        //print 'CIE94: ' . rgb_CIE94($rgb, $black) . "\n";
    }
    //print "\x1b[0m";
    }
    //print "\n";
}
?>
