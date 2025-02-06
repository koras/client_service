<?php

namespace Unit;

use App\Contracts\Services\QrCodeGeneratorInterface;
use Tests\TestCase;

class QrCodeGeneratorTest extends TestCase
{
    private string $data;
    private string $base64StartString;

    private string $assertingBase64String;

    private QrCodeGeneratorInterface $qrCodeGenerator;
    protected function setUp(): void
    {
        parent::setUp();
        $this->base64StartString = 'data:image/png;base64,';
        $this->data = 'MGC208967200';
        $this->assertingBase64String = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAIIAAACCCAAAAACvaE8hAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAAAmJLR0QA/4ePzL8AAAUhSURBVHja7ZtNTFxVFMd/w9CQQgwspBRoC4sqGtIBN1Jqqoko3ZguDE3dEdJooi5MZGE06EJZmCZoNLXGBWkaY2JTulEXDhYXRupHjMEmRsdUYsE28kgUTcFILnNc3AdzZ+57z3nMlA97z+q9c+457//enHPPOffegRg0I2vkaU5KRERS5qBJEZGZGFYr2HRyEByEVaosUf+x8kFYjBx1dCJEcPmycePVAOw0OD0fRJqtyf8K1WX4pNVFcAxacu64dSPiq+8teWKgkPPLp2G2PgTgwdZC/hmxhrZ32eqLIiKjNr9BRER6jDQ1aYpTmUwmkwlNUz0iItJgmx0VEVksz7xwp3NHB2Ez01TLeQCO2RItaLn5EJr7AOYDJA/UO1+4laumP62b2yo2CMKOpwA4XQeAvtmlc8ShDYJQ9xbAyml992YSeNG5o4NQzqBMNMSrwqGhqNreHpUIgzAwEPMFkr8VM2ra+YKDEC8ijhat8fULADz8HJDt1bzxsDd5KAaEiaIhKD20G0B8NQkZO+F8wUHYmMLtNeP6jZIgxFkt3mNcD+vm/iOARwatkTHMVu4t7SuGqCf2OnfcfhFxKaB3bgayXxalfymSVXlvofDaVQCaWqPNnhcRWYj/Oul0Op1Om5weKSS9AKCXu3yqLOMXvaceGHfuuD4IsvkRkQhgzpfNvLZUH/mIVXccMiZ+cyWr4XErLwxZHJ9VZQh8e7rfz+bec7cWnAiAG70GrWnEj70cR60WczmWH5QjIiL/+OJsYWhmXERs26opa4fuisWRbJhcC5IhenkQkilLvgOAXqshmNe+biq8NGywBgdz13P6IQu1wJgZbikbQn9/7K/4ncV5+xAwu09PCBcBdocp3/2+c0cHISgiugG4sH9N8NdhHRHfROt3APBtMsd5Mmzo/RbnB62tlFJKHde8TG4aXzBzxIoyyDxOY+aIrFJKqdUcoZRSakVnEqWUUnqdmimllMrLEZXJsv1ciTxTycBL68654xaKiLxdhHftAcXvMrz3o3Hzh6n3cuImvkHK8zzP80RExC/oJj3P82YKEq2IrEaEUTUd9zzP80ptZeqDWbMxtJ07bqvCLZzsLvaGzfrEuJ4bL9ROTMZ+6n1hgkmAG0d0jjiY4z86lxNf14Xb6F05S5VxNzaDFjV8usNs7m277bXAmCF2p7u2WETMxhi8J8ZcPwuQbCoGwr4YEGasNdVUP8BZ49Rj44jRyvRc3IB54VmAswbjwAGA2UHnjrdcjlgOvYkeu2xD6InWtnf7tMKEucS1yxI3AtCpl6TqDMGJgPWuRYmigCOXQ+Zyl00jlo2Aws25o4MQEZRnnrfk1dPrs3vyJEDnx5bgSDQEmbPkDTEe692+dvmrzn5mxJw6BXDhWNmnpvz2fl1C547bPU39/nRMhdevlA3CwQWAZX8N+jNDUhupd0WfjZxqBebadHP/zvogVNRCbm+pNrZ+LTBnajt33LYRce2LEMHn+uRhd7PB011sX5itsfVBuKpn+aG+wlk3rXdlJpuBxildsumCL5sAnnlCt98ALb74XGk5oiPy43ZY5az5l5aqDlxzXwBhi+7cF0U/AVTsDxau/Gx3+38D0FIFqOmwoGwfLb7QGB4GSBmbhIe1dhPA9TZL4VU/R3QA021hELq6SvmgvW6C/p/kiKWSTS3rkn3nf8TYUhiEmrgP1NuK5qn9V/wcEbKe26g1Og3tc/Hf02ru82h1V8b+2382+IDZonNHByGf/gVhAkeDCCjhzgAAAABJRU5ErkJggg==';
        $this->qrCodeGenerator = app(QrCodeGeneratorInterface::class);
    }

    /**
     * @covers \App\Services\QrCodeGenerator::getBase64
     * @return void
     */
    public function testGetBase64ReturnsNullWhenCodeNotGenerated()
    {
        $result = $this->qrCodeGenerator->getBase64();
        $this->assertNull($result);
    }

    /**
     * @covers \App\Services\QrCodeGenerator::generate
     * @covers \App\Services\QrCodeGenerator::getBase64
     * @return void
     */
    public function testGetBase64ReturnsBase64StringWhenCodeGenerated()
    {
        $result = $this->qrCodeGenerator
            ->generate($this->data)
            ->getBase64();

        self::assertStringStartsWith($this->base64StartString, $result);
        self::assertEquals($this->assertingBase64String, $result);
    }
}
